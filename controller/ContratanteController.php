<?php
/**
 * Controlador del Contratante
 * Maneja la gestión de contratos por parte del contratante
 */

require_once __DIR__ . '/../includes/DocumentProcessor.php';

class ContratanteController {
    
    private $db;
    private $porPagina = 9;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Obtener estadísticas del contratante
     */
    public function obtenerEstadisticas($contratanteCedula) {
        try {
            $stats = [
                'total_contratos' => 0,
                'contratos_activos' => 0,
                'contratos_pendientes' => 0,
                'contratos_mes' => 0
            ];
            
            // Total contratos
            $sql = "SELECT COUNT(*) FROM contratos WHERE contratante_cedula = :cedula";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->execute();
            $stats['total_contratos'] = $stmt->fetchColumn();
            
            // Contratos activos
            $sql = "SELECT COUNT(*) FROM contratos WHERE contratante_cedula = :cedula AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->execute();
            $stats['contratos_activos'] = $stmt->fetchColumn();
            
            // Contratos pendientes de datos
            $sql = "SELECT COUNT(*) FROM contratos WHERE contratante_cedula = :cedula AND datos_completos = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->execute();
            $stats['contratos_pendientes'] = $stmt->fetchColumn();
            
            // Contratos este mes
            $sql = "SELECT COUNT(*) FROM contratos WHERE contratante_cedula = :cedula AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->execute();
            $stats['contratos_mes'] = $stmt->fetchColumn();
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [
                'total_contratos' => 0,
                'contratos_activos' => 0,
                'contratos_pendientes' => 0,
                'contratos_mes' => 0
            ];
        }
    }
    
    /**
     * Obtener contratos recientes del contratante
     */
    public function obtenerContratosRecientes($contratanteCedula, $limite = 5) {
        try {
            $sql = "SELECT c.*, u.nombre as empleado_nombre 
                    FROM contratos c
                    LEFT JOIN usuarios u ON c.empleado_cedula = u.cedula
                    WHERE c.contratante_cedula = :cedula
                    ORDER BY c.created_at DESC
                    LIMIT :limite";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener contratos recientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener contratos paginados del contratante
     */
    public function obtenerContratosPaginados($contratanteCedula, $pagina = 1, $filtros = []) {
        try {
            $offset = ($pagina - 1) * $this->porPagina;
            $params = [':cedula' => $contratanteCedula];
            
            $where = "c.contratante_cedula = :cedula";
            
            if (!empty($filtros['empresa'])) {
                $where .= " AND c.empresa_id = :empresa";
                $params[':empresa'] = $filtros['empresa'];
            }
            
            if (!empty($filtros['estado'])) {
                if ($filtros['estado'] === 'pendiente') {
                    $where .= " AND c.datos_completos = 0";
                } else {
                    $where .= " AND c.estado = :estado";
                    $params[':estado'] = $filtros['estado'];
                }
            }
            
            if (!empty($filtros['q'])) {
                $where .= " AND (u.nombre LIKE :busqueda OR c.cargo LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['q'] . '%';
            }
            
            // Contar total
            $sqlCount = "SELECT COUNT(*) FROM contratos c 
                         LEFT JOIN usuarios u ON c.empleado_cedula = u.cedula
                         WHERE $where";
            $stmtCount = $this->db->prepare($sqlCount);
            foreach ($params as $key => $value) {
                $stmtCount->bindValue($key, $value);
            }
            $stmtCount->execute();
            $total = $stmtCount->fetchColumn();
            
            // Obtener contratos
            $sql = "SELECT c.*, 
                    u.nombre as empleado_nombre,
                    e.nombre as empresa_nombre
                    FROM contratos c
                    LEFT JOIN usuarios u ON c.empleado_cedula = u.cedula
                    LEFT JOIN empresas e ON c.empresa_id = e.id
                    WHERE $where
                    ORDER BY c.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $this->porPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'contratos' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => $total,
                'total_paginas' => ceil($total / $this->porPagina),
                'pagina_actual' => $pagina
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener contratos: " . $e->getMessage());
            return [
                'contratos' => [],
                'total' => 0,
                'total_paginas' => 0,
                'pagina_actual' => 1
            ];
        }
    }
    
    /**
     * Obtener empresas con conteo de contratos del contratante
     */
    public function obtenerEmpresas($contratanteCedula = null) {
        try {
            if ($contratanteCedula) {
                $sql = "SELECT e.*, 
                        (SELECT COUNT(*) FROM contratos c WHERE c.empresa_id = e.id AND c.contratante_cedula = :cedula) as mis_contratos
                        FROM empresas e 
                        WHERE e.estado = 'activo'
                        ORDER BY e.nombre";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $sql = "SELECT * FROM empresas WHERE estado = 'activo' ORDER BY nombre";
                $stmt = $this->db->query($sql);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener empresas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener empresa por ID
     */
    public function obtenerEmpresa($id) {
        try {
            $sql = "SELECT * FROM empresas WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener empresa: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener contratos por empresa del contratante
     */
    public function obtenerContratosPorEmpresa($contratanteCedula, $empresaId, $pagina = 1) {
        try {
            $offset = ($pagina - 1) * $this->porPagina;
            
            // Contar total
            $sqlCount = "SELECT COUNT(*) FROM contratos WHERE contratante_cedula = :cedula AND empresa_id = :empresa_id";
            $stmtCount = $this->db->prepare($sqlCount);
            $stmtCount->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmtCount->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmtCount->execute();
            $total = $stmtCount->fetchColumn();
            
            // Obtener contratos
            $sql = "SELECT c.*, u.nombre as empleado_nombre
                    FROM contratos c
                    LEFT JOIN usuarios u ON c.empleado_cedula = u.cedula
                    WHERE c.contratante_cedula = :cedula AND c.empresa_id = :empresa_id
                    ORDER BY c.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $this->porPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'contratos' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => $total,
                'total_paginas' => ceil($total / $this->porPagina),
                'pagina_actual' => $pagina
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener contratos por empresa: " . $e->getMessage());
            return [
                'contratos' => [],
                'total' => 0,
                'total_paginas' => 0,
                'pagina_actual' => 1
            ];
        }
    }
    
    /**
     * Obtener empleados disponibles
     */
    public function obtenerEmpleados() {
        try {
            $sql = "SELECT cedula, nombre FROM usuarios 
                    WHERE rol IN ('empleado', 'aspirante') AND estado = 'activo'
                    ORDER BY nombre";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener empleados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Subir plantilla de contrato (solo archivo .docx)
     */
    public function subirPlantilla($empresaId, $archivo, $contratanteCedula) {
        try {
            // Validar archivo
            if (!isset($archivo['tmp_name']) || empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
                return ['success' => false, 'error' => 'Debe subir un archivo válido'];
            }
            
            // Validar tamaño de archivo (máximo 10MB)
            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($archivo['size'] > $maxSize) {
                return ['success' => false, 'error' => 'El archivo es demasiado grande. Máximo 10MB'];
            }
            
            if ($archivo['size'] == 0) {
                return ['success' => false, 'error' => 'El archivo está vacío'];
            }
            
            // Validar tipo de archivo
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['docx'];
            if (!in_array($extension, $allowedExtensions)) {
                return ['success' => false, 'error' => 'Solo se permiten archivos .docx'];
            }
            
            // Validar tipo MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $archivo['tmp_name']);
            finfo_close($finfo);
            
            $allowedMimeTypes = [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip' // Los .docx son ZIP internamente
            ];
            
            if (!in_array($mimeType, $allowedMimeTypes)) {
                return ['success' => false, 'error' => 'Tipo de archivo no permitido'];
            }
            
            // Validar empresa
            if (empty($empresaId) || !is_numeric($empresaId)) {
                return ['success' => false, 'error' => 'Empresa inválida'];
            }
            
            // Crear directorio si no existe
            $uploadDir = 'uploads/contratos/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    return ['success' => false, 'error' => 'Error al crear directorio de almacenamiento'];
                }
            }
            
            // Sanitizar nombre de archivo
            $nombreOriginal = pathinfo($archivo['name'], PATHINFO_FILENAME);
            $nombreOriginal = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '', $nombreOriginal);
            $nombreOriginal = substr($nombreOriginal, 0, 100); // Limitar longitud
            
            // Generar nombre único manteniendo nombre original
            $nombreArchivo = $nombreOriginal . '_' . time() . '.docx';
            $rutaArchivo = $uploadDir . $nombreArchivo;
            
            // Validar que no exista el archivo (aunque es poco probable con timestamp)
            if (file_exists($rutaArchivo)) {
                $nombreArchivo = $nombreOriginal . '_' . time() . '_' . uniqid() . '.docx';
                $rutaArchivo = $uploadDir . $nombreArchivo;
            }
            
            // Mover archivo
            if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                return ['success' => false, 'error' => 'Error al subir el archivo'];
            }
            
            // Validar que el archivo se movió correctamente
            if (!file_exists($rutaArchivo) || filesize($rutaArchivo) == 0) {
                return ['success' => false, 'error' => 'Error al guardar el archivo'];
            }
            
            // Validar que la empresa existe
            $empresa = $this->obtenerEmpresa($empresaId);
            if (!$empresa) {
                unlink($rutaArchivo);
                return ['success' => false, 'error' => 'Empresa no encontrada'];
            }
            
            // Insertar registro (solo con empresa, contratante y archivo)
            $sql = "INSERT INTO contratos (empresa_id, contratante_cedula, archivo_contrato, estado, fecha_firma)
                    VALUES (:empresa_id, :contratante_cedula, :archivo, 'activo', CURDATE())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindParam(':contratante_cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->bindParam(':archivo', $rutaArchivo);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'created', 'id' => $this->db->lastInsertId()];
            }
            
            // Si falla, eliminar archivo
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
            return ['success' => false, 'error' => 'Error al guardar la plantilla'];
            
        } catch (PDOException $e) {
            error_log("Error al subir plantilla: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al subir la plantilla'];
        }
    }
    
    /**
     * Obtener contrato por ID
     */
    public function obtenerContrato($id, $contratanteCedula = null) {
        try {
            $sql = "SELECT c.*, 
                    u.nombre as empleado_nombre,
                    e.nombre as empresa_nombre
                    FROM contratos c
                    LEFT JOIN usuarios u ON c.empleado_cedula = u.cedula
                    LEFT JOIN empresas e ON c.empresa_id = e.id
                    WHERE c.id = :id";
            
            if ($contratanteCedula) {
                $sql .= " AND c.contratante_cedula = :cedula";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($contratanteCedula) {
                $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener contrato: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener datos adicionales del contrato
     */
    public function obtenerDatosContrato($contratoId) {
        try {
            $sql = "SELECT * FROM datos_contrato WHERE contrato_id = :contrato_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error al obtener datos del contrato: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Guardar datos adicionales del contrato
     */
    public function guardarDatosContrato($contratoId, $datos) {
        try {
            // Verificar si ya existen datos
            $existentes = $this->obtenerDatosContrato($contratoId);
            
            if ($existentes) {
                // Actualizar
                $sql = "UPDATE datos_contrato SET 
                        direccion = :direccion,
                        telefono = :telefono,
                        email = :email,
                        fecha_nacimiento = :fecha_nacimiento,
                        lugar_nacimiento = :lugar_nacimiento,
                        estado_civil = :estado_civil,
                        jornada_laboral = :jornada_laboral,
                        horario = :horario,
                        lugar_trabajo = :lugar_trabajo,
                        area_departamento = :area_departamento,
                        banco = :banco,
                        tipo_cuenta = :tipo_cuenta,
                        numero_cuenta = :numero_cuenta,
                        eps = :eps,
                        fondo_pension = :fondo_pension,
                        fondo_cesantias = :fondo_cesantias,
                        caja_compensacion = :caja_compensacion,
                        contacto_emergencia_nombre = :contacto_emergencia_nombre,
                        contacto_emergencia_telefono = :contacto_emergencia_telefono,
                        contacto_emergencia_parentesco = :contacto_emergencia_parentesco,
                        observaciones = :observaciones
                        WHERE contrato_id = :contrato_id";
            } else {
                // Insertar
                $sql = "INSERT INTO datos_contrato (contrato_id, direccion, telefono, email, 
                        fecha_nacimiento, lugar_nacimiento, estado_civil, jornada_laboral, horario,
                        lugar_trabajo, area_departamento, banco, tipo_cuenta, numero_cuenta,
                        eps, fondo_pension, fondo_cesantias, caja_compensacion,
                        contacto_emergencia_nombre, contacto_emergencia_telefono, 
                        contacto_emergencia_parentesco, observaciones)
                        VALUES (:contrato_id, :direccion, :telefono, :email, 
                        :fecha_nacimiento, :lugar_nacimiento, :estado_civil, :jornada_laboral, :horario,
                        :lugar_trabajo, :area_departamento, :banco, :tipo_cuenta, :numero_cuenta,
                        :eps, :fondo_pension, :fondo_cesantias, :caja_compensacion,
                        :contacto_emergencia_nombre, :contacto_emergencia_telefono, 
                        :contacto_emergencia_parentesco, :observaciones)";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
            $stmt->bindValue(':direccion', $datos['direccion'] ?? null);
            $stmt->bindValue(':telefono', $datos['telefono'] ?? null);
            $stmt->bindValue(':email', $datos['email'] ?? null);
            $stmt->bindValue(':fecha_nacimiento', $datos['fecha_nacimiento'] ?: null);
            $stmt->bindValue(':lugar_nacimiento', $datos['lugar_nacimiento'] ?? null);
            $stmt->bindValue(':estado_civil', $datos['estado_civil'] ?? null);
            $stmt->bindValue(':jornada_laboral', $datos['jornada_laboral'] ?? null);
            $stmt->bindValue(':horario', $datos['horario'] ?? null);
            $stmt->bindValue(':lugar_trabajo', $datos['lugar_trabajo'] ?? null);
            $stmt->bindValue(':area_departamento', $datos['area_departamento'] ?? null);
            $stmt->bindValue(':banco', $datos['banco'] ?? null);
            $stmt->bindValue(':tipo_cuenta', $datos['tipo_cuenta'] ?? null);
            $stmt->bindValue(':numero_cuenta', $datos['numero_cuenta'] ?? null);
            $stmt->bindValue(':eps', $datos['eps'] ?? null);
            $stmt->bindValue(':fondo_pension', $datos['fondo_pension'] ?? null);
            $stmt->bindValue(':fondo_cesantias', $datos['fondo_cesantias'] ?? null);
            $stmt->bindValue(':caja_compensacion', $datos['caja_compensacion'] ?? null);
            $stmt->bindValue(':contacto_emergencia_nombre', $datos['contacto_emergencia_nombre'] ?? null);
            $stmt->bindValue(':contacto_emergencia_telefono', $datos['contacto_emergencia_telefono'] ?? null);
            $stmt->bindValue(':contacto_emergencia_parentesco', $datos['contacto_emergencia_parentesco'] ?? null);
            $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null);
            
            $stmt->execute();
            
            // Actualizar salario en contrato si se proporcionó
            if (!empty($datos['salario'])) {
                $sqlSalario = "UPDATE contratos SET salario = :salario WHERE id = :id";
                $stmtSalario = $this->db->prepare($sqlSalario);
                $stmtSalario->bindParam(':salario', $datos['salario']);
                $stmtSalario->bindParam(':id', $contratoId, PDO::PARAM_INT);
                $stmtSalario->execute();
            }
            
            // Marcar como datos completos si tiene los campos mínimos
            if (!empty($datos['direccion']) && !empty($datos['telefono'])) {
                $sqlCompleto = "UPDATE contratos SET datos_completos = 1 WHERE id = :id";
                $stmtCompleto = $this->db->prepare($sqlCompleto);
                $stmtCompleto->bindParam(':id', $contratoId, PDO::PARAM_INT);
                $stmtCompleto->execute();
            }
            
            return ['success' => true, 'message' => 'updated'];
            
        } catch (PDOException $e) {
            error_log("Error al guardar datos del contrato: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al guardar los datos'];
        }
    }
    
    /**
     * Eliminar contrato
     */
    public function eliminarContrato($id, $contratanteCedula) {
        try {
            // Obtener contrato para eliminar archivo
            $contrato = $this->obtenerContrato($id, $contratanteCedula);
            
            if (!$contrato) {
                return ['success' => false, 'error' => 'Contrato no encontrado'];
            }
            
            // Eliminar archivo si existe
            if (!empty($contrato['archivo_contrato']) && file_exists($contrato['archivo_contrato'])) {
                @unlink($contrato['archivo_contrato']); // @ para evitar errores si el archivo ya fue eliminado
            }
            
            // Eliminar archivo generado si existe
            if (!empty($contrato['archivo_generado']) && file_exists($contrato['archivo_generado'])) {
                @unlink($contrato['archivo_generado']); // @ para evitar errores si el archivo ya fue eliminado
            }
            
            // Eliminar de BD
            $sql = "DELETE FROM contratos WHERE id = :id AND contratante_cedula = :cedula";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'deleted'];
            }
            
            return ['success' => false, 'error' => 'No se pudo eliminar el contrato'];
            
        } catch (PDOException $e) {
            error_log("Error al eliminar contrato: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al eliminar el contrato'];
        }
    }
    
    /**
     * Extraer campos del documento
     */
    public function extraerCamposDocumento($rutaArchivo) {
        $processor = new DocumentProcessor($rutaArchivo);
        return $processor->extraerCampos();
    }
    
    /**
     * Obtener información de campos para formulario
     */
    public function obtenerInfoCampos($rutaArchivo) {
        $processor = new DocumentProcessor($rutaArchivo);
        $processor->extraerCampos();
        return $processor->obtenerInfoCampos();
    }
    
    /**
     * Generar HTML del formulario
     */
    public function generarFormularioHTML($rutaArchivo, $valoresActuales = []) {
        $processor = new DocumentProcessor($rutaArchivo);
        $processor->extraerCampos();
        $infoCampos = $processor->obtenerInfoCampos();
        return $processor->generarFormularioHTML($infoCampos, $valoresActuales);
    }
    
    /**
     * Guardar campos del contrato
     */
    public function guardarCamposContrato($contratoId, $campos) {
        // Validar contrato ID
        if (empty($contratoId) || !is_numeric($contratoId)) {
            return false;
        }
        
        // Validar que campos sea un array
        if (!is_array($campos)) {
            return false;
        }
        
        // Sanitizar campos
        $camposSanitizados = [];
        foreach ($campos as $nombre => $valor) {
            // Sanitizar nombre del campo
            $nombre = trim($nombre);
            $nombre = preg_replace('/[^a-zA-Z0-9_\.\s\-]/', '', $nombre);
            
            if (empty($nombre)) {
                continue; // Saltar campos con nombres inválidos
            }
            
            // Sanitizar valor
            if (is_array($valor) || is_object($valor)) {
                $valor = ''; // Convertir arrays/objetos a string vacío
            } else {
                $valor = trim((string)$valor);
            }
            
            $camposSanitizados[$nombre] = $valor;
        }
        
        return DocumentProcessor::guardarCamposContrato($this->db, $contratoId, $camposSanitizados);
    }
    
    /**
     * Obtener campos guardados del contrato
     */
    public function obtenerCamposContrato($contratoId) {
        return DocumentProcessor::obtenerCamposContrato($this->db, $contratoId);
    }
    
    /**
     * Generar documento con campos completados
     */
    public function generarDocumentoCompletado($contratoId, $contratanteCedula) {
        try {
            // Obtener contrato
            $contrato = $this->obtenerContrato($contratoId, $contratanteCedula);
            
            if (!$contrato || empty($contrato['archivo_contrato'])) {
                return ['success' => false, 'error' => 'Contrato o archivo no encontrado'];
            }
            
            // Obtener campos guardados
            $campos = $this->obtenerCamposContrato($contratoId);
            
            if (empty($campos)) {
                return ['success' => false, 'error' => 'No hay campos guardados para este contrato'];
            }
            
            // Procesar documento
            $processor = new DocumentProcessor();
            
            // Crear directorio de documentos generados
            $dirGenerados = 'uploads/contratos_generados/';
            if (!is_dir($dirGenerados)) {
                mkdir($dirGenerados, 0755, true);
            }
            
            $nombreArchivo = 'contrato_' . $contratoId . '_' . time() . '.docx';
            $rutaDestino = $dirGenerados . $nombreArchivo;
            
            $resultado = $processor->procesarDocumento(
                $contrato['archivo_contrato'],
                $campos,
                $rutaDestino
            );
            
            if ($resultado['success']) {
                // Actualizar contrato con ruta del archivo generado
                $sql = "UPDATE contratos SET archivo_generado = :archivo, datos_completos = 1 WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':archivo', $rutaDestino);
                $stmt->bindParam(':id', $contratoId, PDO::PARAM_INT);
                $stmt->execute();
                
                return ['success' => true, 'ruta' => $rutaDestino];
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error al generar documento: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al generar el documento'];
        }
    }
    
    /**
     * Crear nuevo aspirante
     */
    public function crearAspirante($datos, $contratanteCedula) {
        try {
            // Validar datos requeridos
            if (empty($datos['cedula']) || empty($datos['nombre']) || 
                empty($datos['telefono']) || empty($datos['correo']) || 
                empty($datos['direccion']) || empty($datos['empresa_id'])) {
                return ['success' => false, 'error' => 'Todos los campos son obligatorios'];
            }
            
            // Validar cédula
            if (!is_numeric($datos['cedula']) || strlen($datos['cedula']) < 5) {
                return ['success' => false, 'error' => 'Cédula inválida'];
            }
            
            // Validar email
            if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'error' => 'Correo electrónico inválido'];
            }
            
            // Iniciar transacción
            $this->db->beginTransaction();
            
            try {
                // Verificar si el usuario ya existe
                $sql = "SELECT cedula FROM usuarios WHERE cedula = :cedula";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':cedula', $datos['cedula'], PDO::PARAM_INT);
                $stmt->execute();
                $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Si no existe, crear usuario con rol aspirante
                if (!$usuarioExistente) {
                    // Generar usuario y contraseña temporal
                    $usuario = 'asp_' . $datos['cedula'];
                    $contrasenaTemporal = 'temp_' . substr(md5($datos['cedula'] . time()), 0, 8);
                    
                    $sql = "INSERT INTO usuarios (cedula, nombre, usuario, contrasena, rol, estado) 
                            VALUES (:cedula, :nombre, :usuario, :contrasena, 'aspirante', 'activo')";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(':cedula', $datos['cedula'], PDO::PARAM_INT);
                    $stmt->bindParam(':nombre', $datos['nombre']);
                    $stmt->bindParam(':usuario', $usuario);
                    $stmt->bindValue(':contrasena', password_hash($contrasenaTemporal, PASSWORD_DEFAULT));
                    $stmt->execute();
                }
                
                // Verificar si ya existe el aspirante para esta empresa
                $sql = "SELECT id FROM aspirantes WHERE cedula = :cedula AND empresa_id = :empresa_id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':cedula', $datos['cedula'], PDO::PARAM_INT);
                $stmt->bindParam(':empresa_id', $datos['empresa_id'], PDO::PARAM_INT);
                $stmt->execute();
                $aspiranteExistente = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($aspiranteExistente) {
                    // Actualizar datos del aspirante
                    $sql = "UPDATE aspirantes SET 
                            telefono = :telefono,
                            telefono2 = :telefono2,
                            correo = :correo,
                            direccion = :direccion,
                            estado = 'activo'
                            WHERE id = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(':id', $aspiranteExistente['id'], PDO::PARAM_INT);
                    $stmt->bindParam(':telefono', $datos['telefono']);
                    $telefono2 = $datos['telefono2'] ?? null;
                    $stmt->bindParam(':telefono2', $telefono2);
                    $stmt->bindParam(':correo', $datos['correo']);
                    $stmt->bindParam(':direccion', $datos['direccion']);
                    $stmt->execute();
                    
                    $aspiranteId = $aspiranteExistente['id'];
                } else {
                    // Crear nuevo aspirante
                    $sql = "INSERT INTO aspirantes (cedula, empresa_id, telefono, telefono2, correo, direccion, estado) 
                            VALUES (:cedula, :empresa_id, :telefono, :telefono2, :correo, :direccion, 'activo')";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(':cedula', $datos['cedula'], PDO::PARAM_INT);
                    $stmt->bindParam(':empresa_id', $datos['empresa_id'], PDO::PARAM_INT);
                    $stmt->bindParam(':telefono', $datos['telefono']);
                    $telefono2 = $datos['telefono2'] ?? null;
                    $stmt->bindParam(':telefono2', $telefono2);
                    $stmt->bindParam(':correo', $datos['correo']);
                    $stmt->bindParam(':direccion', $datos['direccion']);
                    $stmt->execute();
                    
                    $aspiranteId = $this->db->lastInsertId();
                }
                
                // Confirmar transacción
                $this->db->commit();
                
                return [
                    'success' => true, 
                    'aspirante_id' => $aspiranteId,
                    'cedula' => $datos['cedula'],
                    'nombre' => $datos['nombre']
                ];
                
            } catch (PDOException $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (PDOException $e) {
            error_log("Error al crear aspirante: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al crear el aspirante: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener contratos disponibles de una empresa (documentos subidos)
     */
    public function obtenerContratosDisponibles($empresaId, $contratanteCedula) {
        try {
            $sql = "SELECT c.* 
                    FROM contratos c
                    WHERE c.empresa_id = :empresa_id 
                    AND c.contratante_cedula = :cedula
                    AND c.archivo_contrato IS NOT NULL
                    AND c.archivo_contrato != ''
                    ORDER BY c.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener contratos disponibles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener aspirante por ID
     */
    public function obtenerAspirante($aspiranteId) {
        try {
            $sql = "SELECT a.*, u.nombre, u.cedula 
                    FROM aspirantes a
                    INNER JOIN usuarios u ON a.cedula = u.cedula
                    WHERE a.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $aspiranteId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener aspirante: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener aspirantes pendientes del contratante
     * @param int $contratanteCedula Cédula del contratante
     * @param string|null $buscar Término de búsqueda (nombre o cédula)
     * @return array Lista de aspirantes
     */
    public function obtenerAspirantesPendientes($contratanteCedula, $buscar = null) {
        try {
            $sql = "SELECT DISTINCT a.*, u.nombre, u.cedula, e.nombre as empresa_nombre, e.codigo as empresa_codigo,
                    COALESCE((SELECT COUNT(*) FROM aspirante_contratos ac WHERE ac.aspirante_id = a.id), 0) as total_documentos,
                    COALESCE((SELECT COUNT(*) FROM aspirante_contratos ac WHERE ac.aspirante_id = a.id AND ac.estado = 'completado'), 0) as documentos_completados
                    FROM aspirantes a
                    INNER JOIN usuarios u ON a.cedula = u.cedula
                    INNER JOIN empresas e ON a.empresa_id = e.id
                    WHERE a.estado IN ('activo', 'en_proceso')
                    AND EXISTS (
                        SELECT 1 FROM contratos c 
                        WHERE c.empresa_id = a.empresa_id 
                        AND c.contratante_cedula = :cedula
                    )";
            
            // Agregar filtro de búsqueda si se proporciona
            if (!empty($buscar) && trim($buscar) !== '') {
                $buscarLimpio = trim($buscar);
                $sql .= " AND (u.nombre LIKE :buscar OR u.cedula LIKE :buscar)";
            }
            
            $sql .= " ORDER BY a.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            
            // Si hay búsqueda, agregar el parámetro
            if (!empty($buscar) && trim($buscar) !== '') {
                $buscarParam = '%' . $buscarLimpio . '%';
                $stmt->bindParam(':buscar', $buscarParam);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener aspirantes pendientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cambiar estado del aspirante
     */
    public function cambiarEstadoAspirante($aspiranteId, $nuevoEstado, $contratanteCedula) {
        try {
            // Validar estado
            $estadosPermitidos = ['activo', 'en_proceso', 'contratado', 'rechazado'];
            if (!in_array($nuevoEstado, $estadosPermitidos)) {
                return ['success' => false, 'error' => 'Estado inválido'];
            }
            
            // Verificar que el aspirante pertenece a una empresa del contratante
            $sql = "SELECT a.id FROM aspirantes a
                    INNER JOIN contratos c ON c.empresa_id = a.empresa_id
                    WHERE a.id = :aspirante_id AND c.contratante_cedula = :cedula
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':aspirante_id', $aspiranteId, PDO::PARAM_INT);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'error' => 'Aspirante no encontrado o no autorizado'];
            }
            
            // Actualizar estado
            $sql = "UPDATE aspirantes SET estado = :estado WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id', $aspiranteId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Si se marca como contratado, actualizar el rol del usuario a empleado
                if ($nuevoEstado === 'contratado') {
                    $sql = "UPDATE usuarios u
                            INNER JOIN aspirantes a ON u.cedula = a.cedula
                            SET u.rol = 'empleado'
                            WHERE a.id = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(':id', $aspiranteId, PDO::PARAM_INT);
                    $stmt->execute();
                }
                
                return ['success' => true, 'message' => 'Estado actualizado correctamente'];
            }
            
            return ['success' => false, 'error' => 'Error al actualizar el estado'];
        } catch (PDOException $e) {
            error_log("Error al cambiar estado del aspirante: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al cambiar el estado'];
        }
    }
    
    /**
     * Obtener perfil completo del aspirante con documentos
     */
    public function obtenerPerfilAspirante($aspiranteId, $contratanteCedula) {
        try {
            // Verificar que el aspirante pertenece al contratante
            $aspirante = $this->obtenerAspirante($aspiranteId);
            if (!$aspirante) {
                return null;
            }
            
            // Obtener documentos completados (documentos que tienen campos guardados y/o generados)
            // Incluimos documentos que tienen campos guardados, incluso si no están generados aún
            $sql = "SELECT c.*, ac.estado as estado_proceso, ac.created_at as fecha_proceso,
                           (SELECT COUNT(*) FROM campos_contrato WHERE contrato_id = c.id) as tiene_campos
                    FROM contratos c
                    INNER JOIN aspirante_contratos ac ON c.id = ac.contrato_id
                    WHERE ac.aspirante_id = :aspirante_id
                    AND c.contratante_cedula = :cedula
                    AND (c.archivo_generado IS NOT NULL AND c.archivo_generado != '' 
                         OR EXISTS (SELECT 1 FROM campos_contrato WHERE contrato_id = c.id))
                    ORDER BY ac.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':aspirante_id', $aspiranteId, PDO::PARAM_INT);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->execute();
            $documentos_completados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Filtrar y marcar documentos: solo mostrar los que tienen archivo generado (con campos llenos)
            $documentos_completados = array_filter($documentos_completados, function($doc) {
                return !empty($doc['archivo_generado']) && file_exists($doc['archivo_generado']);
            });
            
            // Obtener documentos subidos al perfil
            $sql = "SELECT ad.*, u.nombre as uploaded_by_nombre
                    FROM aspirante_documentos ad
                    LEFT JOIN usuarios u ON ad.uploaded_by = u.cedula
                    WHERE ad.aspirante_id = :aspirante_id
                    ORDER BY ad.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':aspirante_id', $aspiranteId, PDO::PARAM_INT);
            $stmt->execute();
            $documentos_subidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'aspirante' => $aspirante,
                'documentos_completados' => $documentos_completados,
                'documentos_subidos' => $documentos_subidos
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener perfil del aspirante: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Subir documento al perfil del aspirante
     */
    public function subirDocumentoAspirante($aspiranteId, $archivo, $descripcion, $tipoDocumento, $uploadedBy) {
        try {
            // Validar archivo
            if (!isset($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
                return ['success' => false, 'error' => 'No se recibió ningún archivo'];
            }
            
            // Validar tamaño
            if ($archivo['size'] > UPLOAD_MAX_SIZE) {
                return ['success' => false, 'error' => 'El archivo excede el tamaño máximo permitido'];
            }
            
            // Validar tipo
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $tiposPermitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
            if (!in_array($extension, $tiposPermitidos)) {
                return ['success' => false, 'error' => 'Tipo de archivo no permitido'];
            }
            
            // Verificar que el aspirante existe
            $aspirante = $this->obtenerAspirante($aspiranteId);
            if (!$aspirante) {
                return ['success' => false, 'error' => 'Aspirante no encontrado'];
            }
            
            // Crear directorio si no existe
            $dirDocumentos = 'uploads/aspirantes/' . $aspiranteId . '/';
            if (!is_dir($dirDocumentos)) {
                mkdir($dirDocumentos, 0755, true);
            }
            
            // Generar nombre único
            $nombreArchivo = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivo['name']);
            $rutaArchivo = $dirDocumentos . $nombreArchivo;
            
            // Mover archivo
            if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                return ['success' => false, 'error' => 'Error al guardar el archivo'];
            }
            
            // Guardar en base de datos
            $sql = "INSERT INTO aspirante_documentos 
                    (aspirante_id, nombre_archivo, ruta_archivo, tipo_documento, descripcion, uploaded_by)
                    VALUES (:aspirante_id, :nombre_archivo, :ruta_archivo, :tipo_documento, :descripcion, :uploaded_by)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':aspirante_id', $aspiranteId, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_archivo', $archivo['name']);
            $stmt->bindParam(':ruta_archivo', $rutaArchivo);
            $stmt->bindParam(':tipo_documento', $tipoDocumento);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':uploaded_by', $uploadedBy, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Documento subido exitosamente', 'id' => $this->db->lastInsertId()];
            
        } catch (PDOException $e) {
            error_log("Error al subir documento del aspirante: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al subir el documento'];
        }
    }
    
    /**
     * Convertir DOCX a PDF (requiere librería adicional)
     * Por ahora, retorna el DOCX directamente
     */
    public function convertirDocxAPdf($rutaDocx) {
        // TODO: Implementar conversión a PDF usando una librería como:
        // - LibreOffice (comando del sistema)
        // - PhpOffice/PhpWord con renderizador PDF
        // - API externa
        
        // Por ahora, retornamos el DOCX
        return ['success' => false, 'error' => 'Conversión a PDF no implementada aún. Use el archivo DOCX.'];
    }
    
    /**
     * Generar PDF desde DOCX usando LibreOffice (si está disponible)
     * En Windows, intenta diferentes rutas comunes de LibreOffice
     */
    public function generarPDFDesdeDocx($rutaDocx) {
        if (!file_exists($rutaDocx)) {
            return ['success' => false, 'error' => 'Archivo no encontrado'];
        }
        
        $rutaPdf = str_replace('.docx', '.pdf', $rutaDocx);
        
        // Si el PDF ya existe, retornarlo directamente
        if (file_exists($rutaPdf)) {
            return ['success' => true, 'ruta' => $rutaPdf];
        }
        
        // Detectar sistema operativo
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        // Rutas posibles de LibreOffice
        $libreOfficePaths = [];
        
        if ($isWindows) {
            // Rutas comunes en Windows
            $programFiles = getenv('ProgramFiles') ?: 'C:\\Program Files';
            $programFilesX86 = getenv('ProgramFiles(x86)') ?: 'C:\\Program Files (x86)';
            
            $libreOfficePaths = [
                $programFiles . '\\LibreOffice\\program\\soffice.exe',
                $programFilesX86 . '\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
                'libreoffice', // Si está en el PATH
            ];
        } else {
            // Linux/Mac
            $libreOfficePaths = [
                '/usr/bin/libreoffice',
                '/usr/local/bin/libreoffice',
                'libreoffice', // Si está en el PATH
            ];
        }
        
        $libreOfficeExe = null;
        foreach ($libreOfficePaths as $path) {
            if ($isWindows) {
                // En Windows, verificar si el ejecutable existe
                if ($path === 'libreoffice' || file_exists($path)) {
                    $libreOfficeExe = $path;
                    break;
                }
            } else {
                // En Linux/Mac, verificar si es ejecutable
                if ($path === 'libreoffice' || (file_exists($path) && is_executable($path))) {
                    $libreOfficeExe = $path;
                    break;
                }
            }
        }
        
        // Si no se encuentra LibreOffice, intentar usar DomPDF como alternativa
        if (!$libreOfficeExe) {
            // Intentar usar DomPDF a través de DocumentProcessor
            try {
                $processor = new DocumentProcessor();
                $resultadoDomPDF = $processor->convertirDocxAPdf($rutaDocx, $rutaPdf);
                
                if ($resultadoDomPDF['success']) {
                    return $resultadoDomPDF;
                }
                
                // Si DomPDF también falla, retornar error con ambas opciones
                return [
                    'success' => false, 
                    'error' => 'No se pudo convertir a PDF. LibreOffice no está disponible y DomPDF falló: ' . ($resultadoDomPDF['error'] ?? 'Error desconocido'),
                    'alternativa' => 'docx'
                ];
            } catch (Exception $e) {
                return [
                    'success' => false, 
                    'error' => 'LibreOffice no está instalado y DomPDF no está disponible. Para convertir a PDF, instale LibreOffice o DomPDF (composer require dompdf/dompdf).',
                    'alternativa' => 'docx'
                ];
            }
        }
        
        // Construir comando según el sistema operativo
        $dirDestino = dirname($rutaPdf);
        $archivoOrigen = $rutaDocx;
        
        if ($isWindows) {
            // En Windows, usar comillas y rutas absolutas
            $comando = '"' . $libreOfficeExe . '" --headless --convert-to pdf --outdir "' . $dirDestino . '" "' . $archivoOrigen . '" 2>&1';
        } else {
            // En Linux/Mac
            $comando = escapeshellarg($libreOfficeExe) . ' --headless --convert-to pdf --outdir ' . escapeshellarg($dirDestino) . ' ' . escapeshellarg($archivoOrigen) . ' 2>&1';
        }
        
        $output = [];
        $return_var = 0;
        exec($comando, $output, $return_var);
        
        // Verificar si se creó el PDF
        if (file_exists($rutaPdf)) {
            return ['success' => true, 'ruta' => $rutaPdf];
        }
        
        // Si no se creó, verificar si hay un PDF con nombre diferente (LibreOffice puede cambiar el nombre)
        $nombreBase = pathinfo($rutaDocx, PATHINFO_FILENAME);
        $pdfAlternativo = $dirDestino . DIRECTORY_SEPARATOR . $nombreBase . '.pdf';
        
        if (file_exists($pdfAlternativo)) {
            return ['success' => true, 'ruta' => $pdfAlternativo];
        }
        
        // Si falló, retornar error con detalles
        $errorMsg = 'Error al convertir a PDF. ';
        if (!empty($output)) {
            $errorMsg .= 'Detalles: ' . implode(' ', array_slice($output, 0, 3));
        }
        
        return [
            'success' => false, 
            'error' => $errorMsg,
            'alternativa' => 'docx',
            'debug' => [
                'comando' => $comando,
                'return_var' => $return_var,
                'output' => $output
            ]
        ];
    }
    
    /**
     * Obtener empleados de una empresa específica del contratante
     * Incluye empleados que fueron aspirantes contratados, incluso si no tienen documentos generados aún
     */
    public function obtenerEmpleadosPorEmpresa($empresaId, $contratanteCedula) {
        try {
            // Primero, obtener empleados que tienen contratos (con o sin archivo_generado)
            $sql = "SELECT DISTINCT u.cedula, u.nombre, u.estado,
                    (SELECT COUNT(*) FROM contratos c 
                     WHERE c.empleado_cedula = u.cedula 
                     AND c.empresa_id = :empresa_id 
                     AND c.contratante_cedula = :cedula
                     AND c.archivo_generado IS NOT NULL 
                     AND c.archivo_generado != '') as total_documentos,
                    (SELECT MAX(c.fecha_firma) FROM contratos c 
                     WHERE c.empleado_cedula = u.cedula 
                     AND c.empresa_id = :empresa_id 
                     AND c.contratante_cedula = :cedula) as ultimo_contrato_fecha
                    FROM usuarios u
                    INNER JOIN contratos c ON c.empleado_cedula = u.cedula
                    WHERE u.rol = 'empleado'
                    AND c.empresa_id = :empresa_id
                    AND c.contratante_cedula = :cedula
                    GROUP BY u.cedula, u.nombre, u.estado";
            
            // También incluir empleados que fueron aspirantes contratados de esta empresa
            // aunque no tengan contratos aún, pero solo si el contratante tiene acceso a la empresa
            $sql2 = "SELECT DISTINCT u.cedula, u.nombre, u.estado,
                     0 as total_documentos,
                     NULL as ultimo_contrato_fecha
                     FROM usuarios u
                     INNER JOIN aspirantes a ON a.cedula = u.cedula
                     WHERE u.rol = 'empleado'
                     AND a.empresa_id = :empresa_id
                     AND a.estado = 'contratado'
                     AND EXISTS (
                         SELECT 1 FROM contratos c 
                         WHERE c.empresa_id = :empresa_id 
                         AND c.contratante_cedula = :cedula
                     )
                     AND NOT EXISTS (
                         SELECT 1 FROM contratos c 
                         WHERE c.empleado_cedula = u.cedula 
                         AND c.empresa_id = :empresa_id 
                         AND c.contratante_cedula = :cedula
                     )";
            
            // Combinar ambas consultas
            $sql = "($sql) UNION ($sql2) ORDER BY nombre ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindParam(':cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener empleados por empresa: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener perfil completo del empleado con documentos
     * Incluye documentos generados y documentos subidos (si fue aspirante antes)
     */
    public function obtenerPerfilEmpleado($empleadoCedula, $empresaId, $contratanteCedula) {
        try {
            // Verificar que el empleado existe y pertenece a la empresa del contratante
            // También verificar si fue aspirante antes
            // Primero intentar obtener desde contratos
            $sql = "SELECT DISTINCT u.*, e.nombre as empresa_nombre, a.id as aspirante_id
                    FROM usuarios u
                    INNER JOIN contratos c ON c.empleado_cedula = u.cedula
                    INNER JOIN empresas e ON c.empresa_id = e.id
                    LEFT JOIN aspirantes a ON a.cedula = u.cedula AND a.empresa_id = :empresa_id
                    WHERE u.cedula = :cedula
                    AND c.empresa_id = :empresa_id
                    AND c.contratante_cedula = :contratante_cedula
                    AND u.rol = 'empleado'
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $empleadoCedula, PDO::PARAM_INT);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindParam(':contratante_cedula', $contratanteCedula, PDO::PARAM_INT);
            $stmt->execute();
            $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si no se encontró desde contratos, intentar desde aspirantes (empleado contratado sin contratos aún)
            if (!$empleado) {
                $sql = "SELECT DISTINCT u.*, e.nombre as empresa_nombre, a.id as aspirante_id
                        FROM usuarios u
                        INNER JOIN aspirantes a ON a.cedula = u.cedula
                        INNER JOIN empresas e ON a.empresa_id = e.id
                        WHERE u.cedula = :cedula
                        AND a.empresa_id = :empresa_id
                        AND a.estado = 'contratado'
                        AND u.rol = 'empleado'
                        AND EXISTS (
                            SELECT 1 FROM contratos c 
                            WHERE c.empresa_id = :empresa_id 
                            AND c.contratante_cedula = :contratante_cedula
                        )
                        LIMIT 1";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':cedula', $empleadoCedula, PDO::PARAM_INT);
                $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
                $stmt->bindParam(':contratante_cedula', $contratanteCedula, PDO::PARAM_INT);
                $stmt->execute();
                $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$empleado) {
                return null;
            }
            
            // Obtener documentos generados del empleado (contratos completados)
            // Incluir documentos que fueron llenados cuando era aspirante
            $aspiranteId = $empleado['aspirante_id'] ?? null;
            
            if ($aspiranteId) {
                // Si tiene aspirante_id, incluir documentos de cuando era aspirante
                $sql = "SELECT DISTINCT c.*, e.nombre as empresa_nombre, c.fecha_firma,
                               CASE 
                                   WHEN ac.contrato_id IS NOT NULL THEN 'aspirante'
                                   ELSE 'empleado'
                               END as origen
                        FROM contratos c
                        INNER JOIN empresas e ON c.empresa_id = e.id
                        LEFT JOIN aspirante_contratos ac ON ac.contrato_id = c.id AND ac.aspirante_id = :aspirante_id
                        WHERE (c.empleado_cedula = :cedula OR ac.aspirante_id = :aspirante_id)
                        AND c.empresa_id = :empresa_id
                        AND c.contratante_cedula = :contratante_cedula
                        AND c.archivo_generado IS NOT NULL
                        AND c.archivo_generado != ''
                        ORDER BY c.fecha_firma DESC, c.created_at DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':cedula', $empleadoCedula, PDO::PARAM_INT);
                $stmt->bindParam(':aspirante_id', $aspiranteId, PDO::PARAM_INT);
                $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
                $stmt->bindParam(':contratante_cedula', $contratanteCedula, PDO::PARAM_INT);
            } else {
                // Si no tiene aspirante_id, solo obtener documentos como empleado
                $sql = "SELECT DISTINCT c.*, e.nombre as empresa_nombre, c.fecha_firma,
                               'empleado' as origen
                        FROM contratos c
                        INNER JOIN empresas e ON c.empresa_id = e.id
                        WHERE c.empleado_cedula = :cedula
                        AND c.empresa_id = :empresa_id
                        AND c.contratante_cedula = :contratante_cedula
                        AND c.archivo_generado IS NOT NULL
                        AND c.archivo_generado != ''
                        ORDER BY c.fecha_firma DESC, c.created_at DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':cedula', $empleadoCedula, PDO::PARAM_INT);
                $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
                $stmt->bindParam(':contratante_cedula', $contratanteCedula, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $documentos_generados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Filtrar solo los que existen físicamente
            $documentos_generados = array_filter($documentos_generados, function($doc) {
                return !empty($doc['archivo_generado']) && file_exists($doc['archivo_generado']);
            });
            
            // Obtener documentos subidos (si fue aspirante antes)
            $documentos_subidos = [];
            if (!empty($empleado['aspirante_id'])) {
                $sql = "SELECT ad.*, u.nombre as uploaded_by_nombre
                        FROM aspirante_documentos ad
                        LEFT JOIN usuarios u ON ad.uploaded_by = u.cedula
                        WHERE ad.aspirante_id = :aspirante_id
                        ORDER BY ad.created_at DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':aspirante_id', $empleado['aspirante_id'], PDO::PARAM_INT);
                $stmt->execute();
                $documentos_subidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return [
                'empleado' => $empleado,
                'documentos_generados' => array_values($documentos_generados),
                'documentos_subidos' => $documentos_subidos
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener perfil del empleado: " . $e->getMessage());
            return null;
        }
    }
}
?>

