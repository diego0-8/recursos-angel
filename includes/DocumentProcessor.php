<?php
/**
 * Procesador de Documentos Word Mejorado
 * Soluciona problemas de etiquetas XML rotas dentro de variables {{VAR}}
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Writer\PDF\DomPDF;

class DocumentProcessor {
    
    private $rutaArchivo;
    private $campos = [];
    
    public function __construct($rutaArchivo = null) {
        // Asegurar que la configuración de compatibilidad XML esté activa
        Settings::setOutputEscapingEnabled(true);
        if ($rutaArchivo) {
            $this->rutaArchivo = $rutaArchivo;
        }
    }
    
    /**
     * Procesa el documento realizando una limpieza previa del XML
     */
    public function procesarDocumento($rutaOrigen, $valores, $rutaDestino = null) {
        try {
            // Validar ruta de origen
            if (empty($rutaOrigen) || !is_string($rutaOrigen)) {
                return ['success' => false, 'error' => 'Ruta de archivo inválida'];
            }
            
            if (!file_exists($rutaOrigen)) {
                return ['success' => false, 'error' => 'El archivo de origen no existe'];
            }
            
            if (!is_readable($rutaOrigen)) {
                return ['success' => false, 'error' => 'No se puede leer el archivo de origen'];
            }
            
            // Validar que es un archivo .docx
            $extension = strtolower(pathinfo($rutaOrigen, PATHINFO_EXTENSION));
            if ($extension !== 'docx') {
                return ['success' => false, 'error' => 'El archivo debe ser un documento .docx'];
            }
            
            // Validar valores
            if (!is_array($valores)) {
                return ['success' => false, 'error' => 'Los valores deben ser un array'];
            }
            
            // 1. Definir ruta de destino
            if (!$rutaDestino) {
                $info = pathinfo($rutaOrigen);
                $dirDestino = $info['dirname'] . '/generados/';
                if (!is_dir($dirDestino)) {
                    if (!mkdir($dirDestino, 0755, true)) {
                        return ['success' => false, 'error' => 'Error al crear directorio de destino'];
                    }
                }
                $rutaDestino = $dirDestino . $info['filename'] . '_final_' . time() . '.docx';
            } else {
                $dirDestino = dirname($rutaDestino);
                if (!is_dir($dirDestino)) {
                    if (!mkdir($dirDestino, 0755, true)) {
                        return ['success' => false, 'error' => 'Error al crear directorio de destino'];
                    }
                }
            }
            
            // Validar que el directorio de destino es escribible
            if (!is_writable($dirDestino)) {
                return ['success' => false, 'error' => 'El directorio de destino no es escribible'];
            }
            
            // 2. Crear una copia temporal limpia del archivo
            // Esto es CRUCIAL: Limpiamos el XML interno antes de que PHPWord lo toque
            $rutaTemporalLimpia = sys_get_temp_dir() . '/' . uniqid('doc_clean_') . '.docx';
            
            if (!copy($rutaOrigen, $rutaTemporalLimpia)) {
                return ['success' => false, 'error' => 'Error al copiar el archivo temporal'];
            }
            
            $this->limpiarXMLInterno($rutaTemporalLimpia);
            
            // 3. Usar TemplateProcessor sobre el archivo limpio
            $templateProcessor = new TemplateProcessor($rutaTemporalLimpia);
            
            // 4. Aplicar valores con múltiples estrategias de coincidencia
            // Nota: PHPWord por defecto usa ${var}, como usas {{var}} debemos forzar el formato
            foreach ($valores as $campo => $valor) {
                // Validar y sanitizar nombre del campo
                if (empty($campo) || !is_string($campo)) {
                    continue; // Saltar campos inválidos
                }
                
                // Sanitizar nombre del campo
                // Permitimos letras (incluye ñ y tildes), números, espacios y puntuación básica
                $campoLimpio = trim($campo);
                $campoLimpio = preg_replace('/[^\p{L}\p{N}_\.\,\;\:\-\(\)\¿\?\¡\!\s]/u', '', $campoLimpio);
                
                if (empty($campoLimpio)) {
                    continue; // Saltar si quedó vacío después de sanitizar
                }
                
                // Validar y sanitizar valor
                if (!is_scalar($valor)) {
                    $valor = ''; // Convertir arrays/objetos a string vacío
                }
                
                // Normalizamos el valor para evitar romper el XML con caracteres especiales
                $valorEscapado = htmlspecialchars((string)$valor ?? '', ENT_XML1, 'UTF-8');
                
                // Normalizaciones adicionales para mejorar coincidencias
                $campoMinus = strtolower($campoLimpio);
                $campoMayus = strtoupper($campoLimpio);
                $campoCapital = ucfirst($campoMinus);
                // Eliminar puntuación para variantes sin signos
                $campoSinPunt = preg_replace('/[^\p{L}\p{N}\s_]/u', '', $campoLimpio);
                $campoSinPuntMinus = strtolower($campoSinPunt);
                // Reemplazar espacios por guion bajo
                $campoUnderscore = preg_replace('/\s+/', '_', $campoLimpio);
                $campoUnderscoreMinus = strtolower($campoUnderscore);

                $variantes = [
                    '{{'.$campoLimpio.'}}',
                    $campoLimpio,
                    '{{'.$campoMinus.'}}',
                    '{{'.$campoMayus.'}}',
                    '{{'.$campoCapital.'}}',
                    '{{'.$campoSinPunt.'}}',
                    '{{'.$campoSinPuntMinus.'}}',
                    '{{'.$campoUnderscore.'}}',
                    '{{'.$campoUnderscoreMinus.'}}',
                ];

                foreach ($variantes as $variante) {
                    try {
                        $templateProcessor->setValue($variante, $valorEscapado);
                    } catch (Exception $e) {
                        // silencioso
                    }
                }
            }
            
            // 5. Guardar
            $templateProcessor->saveAs($rutaDestino);
            
            // Limpieza
            if(file_exists($rutaTemporalLimpia)) {
                unlink($rutaTemporalLimpia);
            }
            
            return ['success' => true, 'archivo' => $rutaDestino];
            
        } catch (Exception $e) {
            error_log("Error crítico procesando documento: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Esta función repara el XML interno del DOCX.
     * Soluciona:
     * 1. Etiquetas de corrección ortográfica (proofErr)
     * 2. Etiquetas de idioma (lang)
     * 3. Corchetes separados: { <tag> { nombre } <tag> } -> {{nombre}}
     */
    private function limpiarXMLInterno($rutaArchivo) {
        // Validar archivo
        if (empty($rutaArchivo) || !file_exists($rutaArchivo) || !is_readable($rutaArchivo)) {
            return;
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($rutaArchivo) === true) {
            $xmlContent = $zip->getFromName('word/document.xml');
            
            if ($xmlContent) {
                // 1. Eliminar ruido básico (correcciones, idiomas, rsid)
                $xmlContent = preg_replace('/<w:proofErr[^>]*\/>/', '', $xmlContent);
                $xmlContent = preg_replace('/<w:lang[^>]*\/>/', '', $xmlContent);
                $xmlContent = preg_replace('/<w:noProof[^>]*\/>/', '', $xmlContent);
                
                // 2. FUSIÓN DE CORCHETES (El problema de {{nombre}})
                // A veces Word guarda "{...tags...{" en lugar de "{{"
                // Buscamos una llave {, seguida de cualquier cosa que NO sea una llave, y otra llave {
                // Y lo reemplazamos por {{
                
                // Fusión de apertura {{
                // Busca: { (tags opcionales) {
                $xmlContent = preg_replace('/\{(<[^>]+>)*\{/', '{{', $xmlContent);
                
                // Fusión de cierre }}
                // Busca: } (tags opcionales) }
                $xmlContent = preg_replace('/\}(<[^>]+>)*\}/', '}}', $xmlContent);
                
                // 3. REPARACIÓN DE CONTENIDO (Lógica mejorada)
                // Busca patrones {{...}} y elimina el XML de adentro
                $pattern = '/(\{\{)(.*?)(\}\})/s';
                
                $xmlContent = preg_replace_callback($pattern, function($matches) {
                    // $matches[2] es lo que hay dentro de {{ }}
                    
                    // Limpiamos TODAS las etiquetas XML dentro
                    $contenidoLimpio = strip_tags($matches[2]);
                    
                    // Normalizamos espacios (Word a veces mete espacios duros)
                    $contenidoLimpio = preg_replace('/\s+/', ' ', $contenidoLimpio);
                    $contenidoLimpio = trim($contenidoLimpio);
                    
                    // Si por algún motivo quedó vacío o es muy largo (falso positivo), lo dejamos igual
                    if (empty($contenidoLimpio) || strlen($contenidoLimpio) > 50) {
                        return $matches[0];
                    }
                    
                    return '{{' . $contenidoLimpio . '}}';
                }, $xmlContent);
                
                // Guardamos el XML reparado
                $zip->deleteName('word/document.xml');
                $zip->addFromString('word/document.xml', $xmlContent);
            }
            
            $zip->close();
        }
    }
    
    /**
     * Extraer campos del documento Word
     */
    public function extraerCampos($rutaArchivo = null) {
        $ruta = $rutaArchivo ?? $this->rutaArchivo;
        
        if (empty($ruta) || !is_string($ruta)) {
            return [];
        }
        
        if (!file_exists($ruta) || !is_readable($ruta)) {
            return [];
        }
        
        // Validar que es un archivo .docx
        $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        if ($extension !== 'docx') {
            return [];
        }
        
        // Usamos la misma lógica de limpieza mejorada para extraer bien los campos
        $zip = new ZipArchive();
        $campos = [];
        
        if ($zip->open($ruta) === true) {
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            
            if ($xml) {
                // Aplicar la misma limpieza agresiva para la extracción
                // 1. Eliminar ruido básico
                $xml = preg_replace('/<w:proofErr[^>]*\/>/', '', $xml);
                $xml = preg_replace('/<w:lang[^>]*\/>/', '', $xml);
                $xml = preg_replace('/<w:noProof[^>]*\/>/', '', $xml);
                
                // 2. Unir corchetes rotos
                $xml = preg_replace('/\{(<[^>]+>)*\{/', '{{', $xml); // Unir {{
                $xml = preg_replace('/\}(<[^>]+>)*\}/', '}}', $xml); // Unir }}
                
                // 3. Limpiar contenido interno de variables
                $pattern = '/(\{\{)(.*?)(\}\})/s';
                $xml = preg_replace_callback($pattern, function($matches) {
                    $contenidoLimpio = trim(strip_tags($matches[2]));
                    $contenidoLimpio = preg_replace('/\s+/', ' ', $contenidoLimpio);
                    $contenidoLimpio = trim($contenidoLimpio);
                    
                    // Si quedó vacío o es muy largo, mantener original
                    if (empty($contenidoLimpio) || strlen($contenidoLimpio) > 50) {
                        return $matches[0];
                    }
                    
                    return '{{' . $contenidoLimpio . '}}';
                }, $xml);
                
                // 4. Extraer texto unificado
                preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $xml, $matchesTexto);
                $textoUnificado = '';
                if (!empty($matchesTexto[1])) {
                    $textoUnificado = implode('', $matchesTexto[1]);
                    $textoUnificado = html_entity_decode($textoUnificado, ENT_XML1, 'UTF-8');
                }
                
                // 5. Buscar {{...}} en el texto unificado
                // Permitimos letras (incluye acentos y ñ), números, espacios y puntuación básica
                preg_match_all('/\{\{([\p{L}\p{N}_\.\,\;\:\-\(\)\¿\?\¡\!\s]+)\}\}/u', $textoUnificado, $matches);
                
                if (!empty($matches[1])) {
                    $campos = array_map(function($campo) {
                        $campo = trim($campo);
                        // Normalizar espacios alrededor de puntos
                        $campo = preg_replace('/\s*\.\s*/', '.', $campo);
                        // Normalizar espacios múltiples
                        $campo = preg_replace('/\s+/', ' ', $campo);
                        return trim($campo);
                    }, $matches[1]);
                    
                    // Eliminar duplicados y vacíos
                    $campos = array_filter($campos, function($campo) {
                        return !empty($campo);
                    });
                    $campos = array_values(array_unique($campos));
                }
            }
        }
        
        $this->campos = $campos;
        return $this->campos;
    }
    
    /**
     * Obtener información de los campos para el formulario
     */
    public function obtenerInfoCampos() {
        $infoCampos = [];
        
        // Definiciones básicas mejoradas
        $definiciones = [
            // Campos simples (recomendados)
            'nombre' => ['label' => 'Nombre Completo', 'type' => 'text', 'required' => true],
            'cedula' => ['label' => 'Número de Cédula', 'type' => 'text', 'required' => true],
            'celular' => ['label' => 'Celular', 'type' => 'tel', 'required' => false],
            'correo' => ['label' => 'Correo Electrónico', 'type' => 'email', 'required' => false],
            'correo_electronico' => ['label' => 'Correo Electrónico', 'type' => 'email', 'required' => false],
            'direccion' => ['label' => 'Dirección', 'type' => 'text', 'required' => false],
            'barrio' => ['label' => 'Barrio', 'type' => 'text', 'required' => false],
            'ciudad_cedula' => ['label' => 'Ciudad de Expedición de Cédula', 'type' => 'text', 'required' => false],
            'ciudad_residencia' => ['label' => 'Ciudad de Residencia', 'type' => 'text', 'required' => false],
            'cartera' => ['label' => 'Nombre de la Cartera', 'type' => 'text', 'required' => false],
            'cargo' => ['label' => 'Cargo', 'type' => 'text', 'required' => false],
            'salario' => ['label' => 'Salario', 'type' => 'number', 'required' => false],
            // Campos con espacios (compatibilidad)
            'nombre empleado' => ['label' => 'Nombre del Empleado', 'type' => 'text', 'required' => true],
            'numero cedula' => ['label' => 'Número de Cédula', 'type' => 'text', 'required' => true],
            'ciudad cedula' => ['label' => 'Ciudad de Expedición de Cédula', 'type' => 'text', 'required' => false],
            'ciudad residencia' => ['label' => 'Ciudad de Residencia', 'type' => 'text', 'required' => false],
            'correo electronico' => ['label' => 'Correo Electrónico', 'type' => 'email', 'required' => false],
            'numero.celular' => ['label' => 'Número de Celular', 'type' => 'tel', 'required' => false],
            'numero celular' => ['label' => 'Número de Celular', 'type' => 'tel', 'required' => false],
            'nombre cartera' => ['label' => 'Nombre de la Cartera', 'type' => 'text', 'required' => false],
        ];
        
        foreach ($this->campos as $campo) {
            $campoNormalizado = strtolower(trim($campo));
            // Normalizar espacios alrededor de puntos para búsqueda
            $campoNormalizadoBusqueda = preg_replace('/\s*\.\s*/', '.', $campoNormalizado);
            
            // Buscar en definiciones (exacto, normalizado, o normalizado sin espacios en puntos)
            if (isset($definiciones[$campo]) || isset($definiciones[$campoNormalizado]) || isset($definiciones[$campoNormalizadoBusqueda])) {
                $definicion = $definiciones[$campo] ?? $definiciones[$campoNormalizado] ?? $definiciones[$campoNormalizadoBusqueda];
                $infoCampos[$campo] = $definicion;
            } else {
                // Campo no definido, crear etiqueta automática
                $label = ucwords(str_replace(['_', '.'], ' ', strtolower($campo)));
                $infoCampos[$campo] = [
                    'label' => $label,
                    'type' => 'text',
                    'required' => false
                ];
            }
        }
        
        return $infoCampos;
    }
    
    /**
     * Generar HTML del formulario dinámico
     */
    public function generarFormularioHTML($infoCampos, $valoresActuales = []) {
        $html = '';
        
        foreach ($infoCampos as $campo => $info) {
            $valor = $valoresActuales[$campo] ?? '';
            $required = $info['required'] ? 'required' : '';
            $requiredMark = $info['required'] ? '<span class="text-danger">*</span>' : '';
            
            $html .= '<div class="col-md-6 mb-3">';
            $html .= '<label for="campo_' . htmlspecialchars($campo) . '" class="form-label">' . htmlspecialchars($info['label']) . ' ' . $requiredMark . '</label>';
            
            switch ($info['type']) {
                case 'textarea':
                    $html .= '<textarea class="form-control" id="campo_' . htmlspecialchars($campo) . '" name="campos[' . htmlspecialchars($campo) . ']" rows="3" ' . $required . '>' . htmlspecialchars($valor) . '</textarea>';
                    break;
                    
                case 'select':
                    $html .= '<select class="form-select" id="campo_' . htmlspecialchars($campo) . '" name="campos[' . htmlspecialchars($campo) . ']" ' . $required . '>';
                    $html .= '<option value="">Seleccionar...</option>';
                    if (isset($info['options'])) {
                        foreach ($info['options'] as $option) {
                            $selected = ($valor === $option) ? 'selected' : '';
                            $html .= '<option value="' . htmlspecialchars($option) . '" ' . $selected . '>' . htmlspecialchars($option) . '</option>';
                        }
                    }
                    $html .= '</select>';
                    break;
                    
                case 'date':
                    $html .= '<input type="date" class="form-control" id="campo_' . htmlspecialchars($campo) . '" name="campos[' . htmlspecialchars($campo) . ']" value="' . htmlspecialchars($valor) . '" ' . $required . '>';
                    break;
                    
                case 'number':
                    $html .= '<input type="number" class="form-control" id="campo_' . htmlspecialchars($campo) . '" name="campos[' . htmlspecialchars($campo) . ']" value="' . htmlspecialchars($valor) . '" ' . $required . '>';
                    break;
                    
                case 'email':
                    $html .= '<input type="email" class="form-control" id="campo_' . htmlspecialchars($campo) . '" name="campos[' . htmlspecialchars($campo) . ']" value="' . htmlspecialchars($valor) . '" ' . $required . '>';
                    break;
                    
                case 'tel':
                    $html .= '<input type="tel" class="form-control" id="campo_' . htmlspecialchars($campo) . '" name="campos[' . htmlspecialchars($campo) . ']" value="' . htmlspecialchars($valor) . '" ' . $required . '>';
                    break;
                    
                default:
                    $html .= '<input type="text" class="form-control" id="campo_' . htmlspecialchars($campo) . '" name="campos[' . htmlspecialchars($campo) . ']" value="' . htmlspecialchars($valor) . '" ' . $required . '>';
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Obtener los campos encontrados
     */
    public function getCampos() {
        return $this->campos;
    }
    
    /**
     * Guardar campos en la base de datos para un contrato
     */
    public static function guardarCamposContrato($db, $contratoId, $campos) {
        try {
            // Validar parámetros
            if (empty($contratoId) || !is_numeric($contratoId)) {
                return false;
            }
            
            if (!is_array($campos) || empty($campos)) {
                return false;
            }
            
            // Iniciar transacción
            $db->beginTransaction();
            
            try {
                // Eliminar campos anteriores
                $sql = "DELETE FROM campos_contrato WHERE contrato_id = :contrato_id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
                $stmt->execute();
                
                // Insertar nuevos campos
                $sql = "INSERT INTO campos_contrato (contrato_id, nombre_campo, valor) VALUES (:contrato_id, :nombre, :valor)";
                $stmt = $db->prepare($sql);
                
                foreach ($campos as $nombre => $valor) {
                    // Validar y sanitizar nombre
                    $nombre = trim($nombre);
                    if (empty($nombre) || strlen($nombre) > 100) {
                        continue; // Saltar campos inválidos
                    }
                    
                    // Sanitizar valor
                    if (is_array($valor) || is_object($valor)) {
                        $valor = '';
                    } else {
                        $valor = trim((string)$valor);
                    }
                    
                    // Limitar tamaño del valor
                    if (strlen($valor) > 65535) {
                        $valor = substr($valor, 0, 65535);
                    }
                    
                    $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
                    $stmt->bindParam(':nombre', $nombre);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->execute();
                }
                
                // Confirmar transacción
                $db->commit();
                return true;
                
            } catch (PDOException $e) {
                // Revertir transacción en caso de error
                $db->rollBack();
                throw $e;
            }
            
        } catch (PDOException $e) {
            error_log("Error al guardar campos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener campos guardados de un contrato
     */
    public static function obtenerCamposContrato($db, $contratoId) {
        try {
            // Validar parámetros
            if (empty($contratoId) || !is_numeric($contratoId)) {
                return [];
            }
            
            $sql = "SELECT nombre_campo, valor FROM campos_contrato WHERE contrato_id = :contrato_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
            $stmt->execute();
            
            $campos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Sanitizar nombre y valor
                $nombre = trim($row['nombre_campo'] ?? '');
                $valor = trim($row['valor'] ?? '');
                
                if (!empty($nombre)) {
                    $campos[$nombre] = $valor;
                }
            }
            
            return $campos;
        } catch (PDOException $e) {
            error_log("Error al obtener campos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Convertir documento DOCX a PDF usando DomPDF
     * Requiere que el documento DOCX ya esté lleno con los campos
     * 
     * @param string $rutaDocx Ruta del archivo DOCX a convertir
     * @param string|null $rutaPdf Ruta de destino del PDF (opcional)
     * @return array Resultado de la conversión
     */
    public function convertirDocxAPdf($rutaDocx, $rutaPdf = null) {
        try {
            // Validar archivo DOCX
            if (empty($rutaDocx) || !is_string($rutaDocx)) {
                return ['success' => false, 'error' => 'Ruta de archivo DOCX inválida'];
            }
            
            if (!file_exists($rutaDocx)) {
                return ['success' => false, 'error' => 'El archivo DOCX no existe'];
            }
            
            if (!is_readable($rutaDocx)) {
                return ['success' => false, 'error' => 'No se puede leer el archivo DOCX'];
            }
            
            // Validar extensión
            $extension = strtolower(pathinfo($rutaDocx, PATHINFO_EXTENSION));
            if ($extension !== 'docx') {
                return ['success' => false, 'error' => 'El archivo debe ser un documento .docx'];
            }
            
            // Definir ruta de destino del PDF
            if (!$rutaPdf) {
                $info = pathinfo($rutaDocx);
                $rutaPdf = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.pdf';
            } else {
                $dirDestino = dirname($rutaPdf);
                if (!is_dir($dirDestino)) {
                    if (!mkdir($dirDestino, 0755, true)) {
                        return ['success' => false, 'error' => 'Error al crear directorio de destino'];
                    }
                }
            }
            
            // Verificar si DomPDF está disponible
            if (!class_exists('Dompdf\Dompdf')) {
                return [
                    'success' => false, 
                    'error' => 'DomPDF no está instalado. Ejecute: composer require dompdf/dompdf'
                ];
            }
            
            // Cargar el documento DOCX usando IOFactory
            $phpWord = IOFactory::load($rutaDocx);
            
            // Crear el writer de PDF usando DomPDF
            $pdfWriter = new DomPDF($phpWord);
            
            // Configurar opciones de PDF
            $pdfWriter->setFont('Arial'); // Fuente por defecto
            
            // Guardar el PDF
            $pdfWriter->save($rutaPdf);
            
            // Verificar que el PDF se creó correctamente
            if (file_exists($rutaPdf) && filesize($rutaPdf) > 0) {
                return [
                    'success' => true, 
                    'ruta' => $rutaPdf,
                    'tamaño' => filesize($rutaPdf)
                ];
            } else {
                return ['success' => false, 'error' => 'El PDF se generó pero está vacío o no se pudo crear'];
            }
            
        } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
            error_log("Error al convertir DOCX a PDF (PHPWord): " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al procesar el documento: ' . $e->getMessage()];
        } catch (\Exception $e) {
            error_log("Error al convertir DOCX a PDF: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error general: ' . $e->getMessage()];
        }
    }
}
?>
