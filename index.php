<?php
/**
 * Archivo principal del Sistema de Recursos Humanos
 * Maneja las rutas y controladores
 */

// Incluir configuración principal
require_once 'config.php';

// Incluir controladores
require_once 'controller/AuthController.php';
require_once 'controller/EmpleadoController.php';
require_once 'controller/ContratoController.php';
require_once 'controller/ContratanteController.php';

// Obtener la acción solicitada (sanitizada)
$action = isset($_GET['action']) ? trim($_GET['action']) : 'login';
$action = preg_replace('/[^a-zA-Z0-9_]/', '', $action); // Solo letras, números y guiones bajos

// Crear instancia del controlador de autenticación
$authController = new AuthController();

// Manejar las diferentes acciones
switch ($action) {
    case 'login':
        $authController->login();
        break;
        
    case 'logout':
        $authController->logout();
        break;
        
    // ==================== ADMINISTRADOR ====================
    case 'admin_dashboard':
    case 'dashboard':
        $authController->requerirRol('administrador');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $estadisticas = [
            'total_empleados' => 0,
            'contratos_activos' => 0,
            'documentos_pendientes' => 0,
            'nuevos_mes' => 0
        ];
        
        try {
            $db = getDBConnection();
            $stmt = $db->query("SELECT COUNT(*) FROM usuarios");
            $estadisticas['total_empleados'] = $stmt->fetchColumn();
            
            $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
            $estadisticas['nuevos_mes'] = $stmt->fetchColumn();
            
            $stmt = $db->query("SELECT COUNT(*) FROM contratos WHERE estado = 'activo'");
            $estadisticas['contratos_activos'] = $stmt->fetchColumn();
        } catch (Exception $e) {
            // Mantener valores por defecto
        }
        
        $empleados_recientes = [];
        $actividad_reciente = [];
        
        include 'views/admin_dashboard.php';
        break;
        
    case 'admin_empleados':
        $authController->requerirRol('administrador');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $empleadoController = new EmpleadoController();
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        $resultado = $empleadoController->obtenerEmpleadosPaginados($pagina);
        $empleados = $resultado['empleados'];
        $total_empleados = $resultado['total'];
        $total_paginas = $resultado['total_paginas'];
        $pagina_actual = $resultado['pagina_actual'];
        
        include 'views/admin_empleados.php';
        break;
        
    case 'guardar_empleado':
        $authController->requerirRol('administrador');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $empleadoController = new EmpleadoController();
            $resultado = $empleadoController->guardar($_POST);
            
            if ($resultado['success']) {
                header('Location: index.php?action=admin_empleados&success=' . $resultado['message']);
            } else {
                header('Location: index.php?action=admin_empleados&error=' . urlencode($resultado['error']));
            }
            exit();
        }
        
        header('Location: index.php?action=admin_empleados');
        exit();
        break;
        
    case 'toggle_empleado':
        $authController->requerirRol('administrador');
        
        $cedula = $_GET['cedula'] ?? null;
        $estado = $_GET['estado'] ?? null;
        $pagina = $_GET['page'] ?? 1;
        
        if ($cedula && $estado) {
            $empleadoController = new EmpleadoController();
            $resultado = $empleadoController->toggleEstado($cedula, $estado);
            
            if ($resultado['success']) {
                header('Location: index.php?action=admin_empleados&page=' . $pagina . '&success=' . $resultado['message']);
            } else {
                header('Location: index.php?action=admin_empleados&page=' . $pagina . '&error=' . urlencode($resultado['error']));
            }
            exit();
        }
        
        header('Location: index.php?action=admin_empleados');
        exit();
        break;
        
    case 'eliminar_empleado':
        $authController->requerirRol('administrador');
        
        $cedula = $_GET['cedula'] ?? null;
        $pagina = $_GET['page'] ?? 1;
        
        if ($cedula) {
            $empleadoController = new EmpleadoController();
            $resultado = $empleadoController->eliminar($cedula);
            
            if ($resultado['success']) {
                header('Location: index.php?action=admin_empleados&page=' . $pagina . '&success=' . $resultado['message']);
            } else {
                header('Location: index.php?action=admin_empleados&page=' . $pagina . '&error=' . urlencode($resultado['error']));
            }
            exit();
        }
        
        header('Location: index.php?action=admin_empleados');
        exit();
        break;
        
    case 'admin_contratos':
        $authController->requerirRol('administrador');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $contratoController = new ContratoController();
        $empresas = $contratoController->obtenerEmpresas();
        
        include 'views/admin_contratos.php';
        break;
        
    case 'ver_contratos_empresa':
        $authController->requerirRol('administrador');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $empresaId = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        if (!$empresaId) {
            header('Location: index.php?action=admin_contratos');
            exit();
        }
        
        $contratoController = new ContratoController();
        $empresa = $contratoController->obtenerEmpresa($empresaId);
        
        if (!$empresa) {
            header('Location: index.php?action=admin_contratos&error=Empresa no encontrada');
            exit();
        }
        
        $resultado = $contratoController->obtenerContratosPorEmpresa($empresaId, $pagina);
        $contratos = $resultado['contratos'];
        $total_contratos = $resultado['total'];
        $total_paginas = $resultado['total_paginas'];
        $pagina_actual = $resultado['pagina_actual'];
        
        $empleados = $contratoController->obtenerEmpleados();
        $contratantes = $contratoController->obtenerContratantes();
        
        include 'views/admin_contratos_empresa.php';
        break;
        
    case 'guardar_contrato':
        $authController->requerirRol('administrador');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contratoController = new ContratoController();
            $resultado = $contratoController->guardar($_POST);
            $empresaId = $_POST['empresa_id'] ?? 0;
            
            if ($resultado['success']) {
                header('Location: index.php?action=ver_contratos_empresa&empresa_id=' . $empresaId . '&success=' . $resultado['message']);
            } else {
                header('Location: index.php?action=ver_contratos_empresa&empresa_id=' . $empresaId . '&error=' . urlencode($resultado['error']));
            }
            exit();
        }
        
        header('Location: index.php?action=admin_contratos');
        exit();
        break;
        
    case 'eliminar_contrato':
        $authController->requerirRol('administrador');
        
        $id = $_GET['id'] ?? null;
        $empresaId = $_GET['empresa_id'] ?? 0;
        
        if ($id) {
            $contratoController = new ContratoController();
            $resultado = $contratoController->eliminar($id);
            
            if ($resultado['success']) {
                header('Location: index.php?action=ver_contratos_empresa&empresa_id=' . $empresaId . '&success=' . $resultado['message']);
            } else {
                header('Location: index.php?action=ver_contratos_empresa&empresa_id=' . $empresaId . '&error=' . urlencode($resultado['error']));
            }
            exit();
        }
        
        header('Location: index.php?action=admin_contratos');
        exit();
        break;
        
    case 'admin_documentos':
        $authController->requerirRol('administrador');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        echo "<h1>Documentos</h1>";
        echo "<p>Esta vista está en desarrollo.</p>";
        echo "<a href='index.php?action=admin_dashboard'>Volver al Inicio</a>";
        break;
        
    // ==================== CONTRATANTE ====================
    case 'contratante_dashboard':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $contratanteController = new ContratanteController();
        $estadisticas = $contratanteController->obtenerEstadisticas($usuario_actual['cedula']);
        $contratos_recientes = $contratanteController->obtenerContratosRecientes($usuario_actual['cedula']);
        
        include 'views/contratante_dashboard.php';
        break;
        
    case 'contratante_contratos':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $contratanteController = new ContratanteController();
        $empresas = $contratanteController->obtenerEmpresas($usuario_actual['cedula']);
        
        include 'views/contratante_contratos.php';
        break;
        
    case 'contratante_aspirante':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : null;
        
        $contratanteController = new ContratanteController();
        $empresas = $contratanteController->obtenerEmpresas($usuario_actual['cedula']);
        $aspirantes_pendientes = $contratanteController->obtenerAspirantesPendientes($usuario_actual['cedula'], $buscar);
        
        include 'views/contratante_aspirante.php';
        break;

    case 'contratante_aspirante_contratar':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=contratante_aspirante&error=Solicitud inválida');
            exit();
        }
        
        $contratanteController = new ContratanteController();
        $resultado = $contratanteController->contratarAspiranteConDatos($_POST, $_FILES, $usuario_actual['cedula']);
        
        if ($resultado['success']) {
            header('Location: index.php?action=contratante_aspirante&success=1');
        } else {
            header('Location: index.php?action=contratante_aspirante&error=' . urlencode($resultado['error']));
        }
        exit();
        break;
        
    case 'contratante_aspirante_accion':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $aspirante_id = isset($_GET['aspirante_id']) ? (int)$_GET['aspirante_id'] : 0;
        $accion = $_GET['accion'] ?? '';
        
        if (!$aspirante_id || empty($accion)) {
            header('Location: index.php?action=contratante_aspirante&error=Parámetros inválidos');
            exit();
        }
        
        // Mapear acciones a estados
        $estados = [
            'seguir' => 'en_proceso',
            'contratado' => 'contratado',
            'desvincular' => 'rechazado'
        ];
        
        $nuevoEstado = $estados[$accion] ?? null;
        
        if (!$nuevoEstado) {
            header('Location: index.php?action=contratante_aspirante&error=Acción inválida');
            exit();
        }
        
        $contratanteController = new ContratanteController();
        $resultado = $contratanteController->cambiarEstadoAspirante($aspirante_id, $nuevoEstado, $usuario_actual['cedula']);
        
        if ($resultado['success']) {
            header('Location: index.php?action=contratante_aspirante&success=1');
        } else {
            header('Location: index.php?action=contratante_aspirante&error=' . urlencode($resultado['error']));
        }
        exit();
        break;
        
    case 'contratante_aspirante_empresa':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $empresaId = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
        $aspirante_id = isset($_GET['aspirante_id']) ? (int)$_GET['aspirante_id'] : 0;
        
        if (!$empresaId) {
            header('Location: index.php?action=contratante_aspirante');
            exit();
        }
        
        $contratanteController = new ContratanteController();
        $empresa = $contratanteController->obtenerEmpresa($empresaId);
        
        if (!$empresa) {
            header('Location: index.php?action=contratante_aspirante&error=Empresa no encontrada');
            exit();
        }
        
        // Si hay un aspirante_id, mostrar documentos
        if ($aspirante_id) {
            $aspirante = $contratanteController->obtenerAspirante($aspirante_id);
            $contratos = $contratanteController->obtenerContratosDisponibles($empresaId, $usuario_actual['cedula']);
            $aspirante_nombre = $aspirante['nombre'] ?? '';
            $aspirante_cedula = $aspirante['cedula'] ?? '';
            
            // Preparar datos de formularios para cada contrato
            $contratos_con_formularios = [];
            if (is_array($contratos) && !empty($contratos)) {
                foreach ($contratos as $contrato) {
                    $campos_documento = [];
                    $formulario_html = '';
                    $valores_guardados = [];
                    
                    if (!empty($contrato['archivo_contrato']) && file_exists($contrato['archivo_contrato'])) {
                        try {
                            $campos_documento = $contratanteController->extraerCamposDocumento($contrato['archivo_contrato']);
                            $valores_guardados = $contratanteController->obtenerCamposContrato($contrato['id']);
                            
                            // Pre-llenar con datos del aspirante si no hay valores guardados
                            if (empty($valores_guardados) && !empty($aspirante)) {
                                $valores_guardados['nombre'] = $aspirante['nombre'] ?? '';
                                $valores_guardados['cedula'] = $aspirante['cedula'] ?? '';
                                $valores_guardados['telefono'] = $aspirante['telefono'] ?? '';
                                $valores_guardados['telefono2'] = $aspirante['telefono2'] ?? '';
                                $valores_guardados['correo'] = $aspirante['correo'] ?? '';
                                $valores_guardados['direccion'] = $aspirante['direccion'] ?? '';
                            }
                            
                            $formulario_html = $contratanteController->generarFormularioHTML($contrato['archivo_contrato'], $valores_guardados);
                        } catch (Exception $e) {
                            error_log("Error al procesar contrato {$contrato['id']}: " . $e->getMessage());
                            error_log("Trace: " . $e->getTraceAsString());
                            $campos_documento = [];
                            $formulario_html = '';
                        } catch (Error $e) {
                            error_log("Error fatal al procesar contrato {$contrato['id']}: " . $e->getMessage());
                            error_log("Trace: " . $e->getTraceAsString());
                            $campos_documento = [];
                            $formulario_html = '';
                        }
                    }
                    
                    $contrato['campos_documento'] = $campos_documento;
                    $contrato['formulario_html'] = $formulario_html;
                    $contratos_con_formularios[] = $contrato;
                }
            }
            $contratos = $contratos_con_formularios;
        } else {
            $aspirante_id = null;
            $aspirante = null;
            $contratos = [];
            $aspirante_nombre = '';
            $aspirante_cedula = '';
        }
        
        include 'views/contratante_aspirante_empresa.php';
        break;
        
    case 'contratante_guardar_aspirante':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contratanteController = new ContratanteController();
            $resultado = $contratanteController->crearAspirante($_POST, $usuario_actual['cedula']);
            
            if ($resultado['success']) {
                header('Location: index.php?action=contratante_aspirante_empresa&empresa_id=' . $_POST['empresa_id'] . '&aspirante_id=' . $resultado['aspirante_id'] . '&success=1');
            } else {
                header('Location: index.php?action=contratante_aspirante_empresa&empresa_id=' . $_POST['empresa_id'] . '&error=' . urlencode($resultado['error']));
            }
            exit();
        }
        
        header('Location: index.php?action=contratante_aspirante');
        exit();
        break;
        
    case 'contratante_contratos_empresa':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $empresaId = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $tipoPlantilla = isset($_GET['tipo']) ? $_GET['tipo'] : 'aspirantes'; // Por defecto aspirantes
        
        // Convertir 'aspirantes' a 'aspirante' y 'empleados' a 'empleado'
        if ($tipoPlantilla === 'aspirantes') {
            $tipoPlantilla = 'aspirante';
        } elseif ($tipoPlantilla === 'empleados') {
            $tipoPlantilla = 'empleado';
        }
        
        if (!$empresaId) {
            header('Location: index.php?action=contratante_contratos');
            exit();
        }
        
        $contratanteController = new ContratanteController();
        $empresa = $contratanteController->obtenerEmpresa($empresaId);
        
        if (!$empresa) {
            header('Location: index.php?action=contratante_contratos&error=Empresa no encontrada');
            exit();
        }
        
        $resultado = $contratanteController->obtenerContratosPorEmpresa($usuario_actual['cedula'], $empresaId, $pagina, $tipoPlantilla);
        $contratos = $resultado['contratos'];
        $total_contratos = $resultado['total'];
        $total_paginas = $resultado['total_paginas'];
        $pagina_actual = $resultado['pagina_actual'];
        
        $empleados = $contratanteController->obtenerEmpleados();
        
        include 'views/contratante_contratos_empresa.php';
        break;
        
    case 'contratante_subir_plantilla':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contratanteController = new ContratanteController();
            $archivo = $_FILES['archivo_contrato'] ?? null;
            $empresaId = $_POST['empresa_id'] ?? 0;
            $tipoPlantilla = $_POST['tipo_plantilla'] ?? 'empleado';
            $resultado = $contratanteController->subirPlantilla($empresaId, $archivo, $usuario_actual['cedula'], $tipoPlantilla);
            
            $tipoParam = $tipoPlantilla ? '&tipo=' . $tipoPlantilla : '';
            if ($resultado['success']) {
                header('Location: index.php?action=contratante_contratos_empresa&empresa_id=' . $empresaId . $tipoParam . '&success=' . $resultado['message']);
            } else {
                header('Location: index.php?action=contratante_contratos_empresa&empresa_id=' . $empresaId . $tipoParam . '&error=' . urlencode($resultado['error']));
            }
            exit();
        }
        
        header('Location: index.php?action=contratante_contratos');
        exit();
        break;
        
    case 'contratante_llenar_contrato':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $contratoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $empresa_id = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
        $aspirante_id = isset($_GET['aspirante_id']) ? (int)$_GET['aspirante_id'] : 0;
        
        if (!$contratoId) {
            header('Location: index.php?action=contratante_contratos');
            exit();
        }
        
        $contratanteController = new ContratanteController();
        $contrato = $contratanteController->obtenerContrato($contratoId, $usuario_actual['cedula']);
        
        if (!$contrato) {
            header('Location: index.php?action=contratante_contratos&error=Contrato no encontrado');
            exit();
        }
        
        // Si hay un aspirante_id, vincularlo al contrato
        if ($aspirante_id) {
            $aspirante = $contratanteController->obtenerAspirante($aspirante_id);
            if ($aspirante) {
                // Actualizar el contrato con la cédula del aspirante
                $sql = "UPDATE contratos SET empleado_cedula = :cedula WHERE id = :id";
                $db = getDBConnection();
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':cedula', $aspirante['cedula'], PDO::PARAM_INT);
                $stmt->bindParam(':id', $contratoId, PDO::PARAM_INT);
                $stmt->execute();
                
                // Vincular aspirante con contrato
                $sql = "INSERT INTO aspirante_contratos (aspirante_id, contrato_id, estado) 
                        VALUES (:aspirante_id, :contrato_id, 'en_proceso')
                        ON DUPLICATE KEY UPDATE estado = 'en_proceso'";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':aspirante_id', $aspirante_id, PDO::PARAM_INT);
                $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
                $stmt->execute();
                
                // Recargar contrato con datos actualizados
                $contrato = $contratanteController->obtenerContrato($contratoId, $usuario_actual['cedula']);
            }
        }
        
        // Extraer campos del documento
        $campos_documento = [];
        $formulario_html = '';
        $valores_guardados = [];
        
        if (!empty($contrato['archivo_contrato']) && file_exists($contrato['archivo_contrato'])) {
            $campos_documento = $contratanteController->extraerCamposDocumento($contrato['archivo_contrato']);
            $valores_guardados = $contratanteController->obtenerCamposContrato($contratoId);
            
            // Si hay un aspirante, pre-llenar con sus datos
            if ($aspirante_id && $aspirante) {
                $valores_guardados['nombre'] = $aspirante['nombre'] ?? '';
                $valores_guardados['cedula'] = $aspirante['cedula'] ?? '';
                $valores_guardados['telefono'] = $aspirante['telefono'] ?? '';
                $valores_guardados['telefono2'] = $aspirante['telefono2'] ?? '';
                $valores_guardados['correo'] = $aspirante['correo'] ?? '';
                $valores_guardados['direccion'] = $aspirante['direccion'] ?? '';
            }
            
            $formulario_html = $contratanteController->generarFormularioHTML($contrato['archivo_contrato'], $valores_guardados);
        }
        
        include 'views/contratante_llenar_contrato.php';
        break;
    
    case 'contratante_guardar_campos_contrato':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contratoId = $_POST['contrato_id'] ?? 0;
            $empresa_id = $_POST['empresa_id'] ?? 0;
            $aspirante_id = isset($_POST['aspirante_id']) ? (int)$_POST['aspirante_id'] : 0;
            $campos = $_POST['campos'] ?? [];
            $accion = $_POST['accion'] ?? 'guardar';
            
            $contratanteController = new ContratanteController();
            
            // Verificar que el contrato pertenece al contratante
            $contrato = $contratanteController->obtenerContrato($contratoId, $usuario_actual['cedula']);
            if (!$contrato) {
                header('Location: index.php?action=contratante_contratos&error=Contrato no encontrado');
                exit();
            }
            
            // Guardar campos
            $guardado = $contratanteController->guardarCamposContrato($contratoId, $campos);
            
            if (!$guardado) {
                $urlRedirect = $aspirante_id 
                    ? 'index.php?action=contratante_aspirante_empresa&empresa_id=' . $empresa_id . '&aspirante_id=' . $aspirante_id
                    : 'index.php?action=contratante_llenar_contrato&id=' . $contratoId . '&empresa_id=' . $empresa_id;
                header('Location: ' . $urlRedirect . '&error=Error al guardar');
                exit();
            }
            
            // SIEMPRE generar el documento cuando se guarda (tanto "guardar" como "generar")
            // Esto asegura que el documento esté disponible en el perfil
            $resultado = $contratanteController->generarDocumentoCompletado($contratoId, $usuario_actual['cedula']);
            
            // Si hay aspirante_id, actualizar estado y vincular
            if ($aspirante_id) {
                $db = getDBConnection();
                
                // Asegurar que existe la relación aspirante_contrato
                $sql = "INSERT INTO aspirante_contratos (aspirante_id, contrato_id, estado) 
                        VALUES (:aspirante_id, :contrato_id, :estado)
                        ON DUPLICATE KEY UPDATE estado = :estado";
                $stmt = $db->prepare($sql);
                $estado = $resultado['success'] ? 'completado' : 'en_proceso';
                $stmt->bindParam(':aspirante_id', $aspirante_id, PDO::PARAM_INT);
                $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
                $stmt->bindParam(':estado', $estado);
                $stmt->execute();
                
                // Si se generó exitosamente, redirigir al perfil del aspirante
                if ($resultado['success']) {
                    header('Location: index.php?action=contratante_perfil_aspirante&aspirante_id=' . $aspirante_id . '&empresa_id=' . $empresa_id . '&success=1');
                } else {
                    // Si hubo error al generar, redirigir a la vista de empresa con error
                    header('Location: index.php?action=contratante_aspirante_empresa&empresa_id=' . $empresa_id . '&aspirante_id=' . $aspirante_id . '&error=' . urlencode($resultado['error']));
                }
            } else {
                // Si no hay aspirante_id, redirigir a la vista normal
                $urlRedirect = 'index.php?action=contratante_llenar_contrato&id=' . $contratoId . '&empresa_id=' . $empresa_id;
                if ($resultado['success']) {
                    header('Location: ' . $urlRedirect . '&success=1');
                } else {
                    header('Location: ' . $urlRedirect . '&error=' . urlencode($resultado['error']));
                }
            }
            exit();
        }
        
        header('Location: index.php?action=contratante_contratos');
        exit();
        break;
        
    case 'contratante_guardar_datos_contrato':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contratoId = $_POST['contrato_id'] ?? 0;
            
            $contratanteController = new ContratanteController();
            
            // Verificar que el contrato pertenece al contratante
            $contrato = $contratanteController->obtenerContrato($contratoId, $usuario_actual['cedula']);
            if (!$contrato) {
                header('Location: index.php?action=contratante_contratos&error=Contrato no encontrado');
                exit();
            }
            
            $resultado = $contratanteController->guardarDatosContrato($contratoId, $_POST);
            
            if ($resultado['success']) {
                header('Location: index.php?action=contratante_llenar_contrato&id=' . $contratoId . '&success=1');
            } else {
                header('Location: index.php?action=contratante_llenar_contrato&id=' . $contratoId . '&error=' . urlencode($resultado['error']));
            }
            exit();
        }
        
        header('Location: index.php?action=contratante_contratos');
        exit();
        break;
        
    case 'contratante_eliminar_contrato':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $id = $_GET['id'] ?? null;
        $empresaId = $_GET['empresa_id'] ?? 0;
        
        if ($id) {
            $contratanteController = new ContratanteController();
            $resultado = $contratanteController->eliminarContrato($id, $usuario_actual['cedula']);
            
            if ($resultado['success']) {
                header('Location: index.php?action=contratante_contratos_empresa&empresa_id=' . $empresaId . '&success=' . $resultado['message']);
            } else {
                header('Location: index.php?action=contratante_contratos_empresa&empresa_id=' . $empresaId . '&error=' . urlencode($resultado['error']));
            }
            exit();
        }
        
        header('Location: index.php?action=contratante_contratos');
        exit();
        break;
        
    case 'contratante_empleados':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $empresa_id = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : null;
        
        $contratanteController = new ContratanteController();
        $empresas = $contratanteController->obtenerEmpresas($usuario_actual['cedula']);
        
        $empresa_seleccionada = null;
        $empleados = [];
        $total_empleados = 0;
        $total_paginas = 0;
        $pagina_actual = 1;
        
        if ($empresa_id) {
            $empresa_seleccionada = $contratanteController->obtenerEmpresa($empresa_id);
            
            if ($empresa_seleccionada) {
                $resultado = $contratanteController->obtenerEmpleadosPorEmpresa($empresa_id, $usuario_actual['cedula'], $pagina, $buscar);
                $empleados = $resultado['empleados'];
                $total_empleados = $resultado['total'];
                $total_paginas = $resultado['total_paginas'];
                $pagina_actual = $resultado['pagina_actual'];
            } else {
                header('Location: index.php?action=contratante_empleados&error=Empresa no encontrada');
                exit();
            }
        }
        
        include 'views/contratante_empleados.php';
        break;
        
    case 'contratante_documentos_empleados':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $empresa_id = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
        
        if (!$empresa_id) {
            header('Location: index.php?action=contratante_empleados&error=Empresa no especificada');
            exit();
        }
        
        $contratanteController = new ContratanteController();
        $empresa = $contratanteController->obtenerEmpresa($empresa_id);
        
        if (!$empresa) {
            header('Location: index.php?action=contratante_empleados&error=Empresa no encontrada');
            exit();
        }
        
        // Obtener documentos de tipo 'empleado' para esta empresa
        $resultado = $contratanteController->obtenerContratosPorEmpresa($usuario_actual['cedula'], $empresa_id, 1, 'empleado');
        $documentos = $resultado['contratos'];
        
        include 'views/contratante_documentos_empleados.php';
        break;
        
    case 'contratante_perfil_empleado':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $empleado_cedula = isset($_GET['empleado_cedula']) ? (int)$_GET['empleado_cedula'] : 0;
        $empresa_id = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
        
        if (!$empleado_cedula || !$empresa_id) {
            header('Location: index.php?action=contratante_empleados&error=Parámetros inválidos');
            exit();
        }
        
        $contratanteController = new ContratanteController();
        $perfil = $contratanteController->obtenerPerfilEmpleado($empleado_cedula, $empresa_id, $usuario_actual['cedula']);
        
        if (!$perfil) {
            header('Location: index.php?action=contratante_empleados&empresa_id=' . $empresa_id . '&error=Empleado no encontrado');
            exit();
        }
        
        $empresa = $contratanteController->obtenerEmpresa($empresa_id);
        // Asegurar que $empresa_id esté disponible en la vista
        // (ya está definido arriba, pero lo mantenemos explícito)
        // También asegurar que $empresa esté disponible
        if (!$empresa) {
            header('Location: index.php?action=contratante_empleados&empresa_id=' . $empresa_id . '&error=Empresa no encontrada');
            exit();
        }
        
        include 'views/contratante_perfil_empleado.php';
        break;
        
    case 'contratante_actualizar_datos_personales':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=contratante_empleados&error=Método no permitido');
            exit();
        }
        
        $aspirante_id = isset($_POST['aspirante_id']) ? (int)$_POST['aspirante_id'] : 0;
        $empleado_cedula = isset($_POST['empleado_cedula']) ? (int)$_POST['empleado_cedula'] : 0;
        $empresa_id = isset($_POST['empresa_id']) ? (int)$_POST['empresa_id'] : 0;
        
        if (!$aspirante_id || !$empleado_cedula || !$empresa_id) {
            header('Location: index.php?action=contratante_perfil_empleado&empleado_cedula=' . $empleado_cedula . '&empresa_id=' . $empresa_id . '&error=Parámetros inválidos');
            exit();
        }
        
        $datos = [
            'telefono' => isset($_POST['telefono']) ? trim($_POST['telefono']) : '',
            'telefono2' => isset($_POST['telefono2']) ? trim($_POST['telefono2']) : '',
            'correo' => isset($_POST['correo']) ? trim($_POST['correo']) : '',
            'direccion' => isset($_POST['direccion']) ? trim($_POST['direccion']) : ''
        ];
        
        $contratanteController = new ContratanteController();
        $resultado = $contratanteController->actualizarDatosPersonales($aspirante_id, $datos, $usuario_actual['cedula']);
        
        if ($resultado['success']) {
            header('Location: index.php?action=contratante_perfil_empleado&empleado_cedula=' . $empleado_cedula . '&empresa_id=' . $empresa_id . '&success=' . urlencode($resultado['message']));
        } else {
            header('Location: index.php?action=contratante_perfil_empleado&empleado_cedula=' . $empleado_cedula . '&empresa_id=' . $empresa_id . '&error=' . urlencode($resultado['error']));
        }
        exit();
        break;
        
    case 'contratante_perfil_aspirante':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $aspirante_id = isset($_GET['aspirante_id']) ? (int)$_GET['aspirante_id'] : 0;
        $empresa_id = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
        
        if (!$aspirante_id) {
            header('Location: index.php?action=contratante_aspirante&error=Aspirante no especificado');
            exit();
        }
        
        $contratanteController = new ContratanteController();
        $perfil = $contratanteController->obtenerPerfilAspirante($aspirante_id, $usuario_actual['cedula']);
        
        if (!$perfil) {
            header('Location: index.php?action=contratante_aspirante&error=Aspirante no encontrado');
            exit();
        }
        
        // Obtener empresa_id del aspirante si no se proporcionó
        if (!$empresa_id && !empty($perfil['aspirante']['empresa_id'])) {
            $empresa_id = $perfil['aspirante']['empresa_id'];
        }
        
        include 'views/contratante_perfil_aspirante.php';
        break;
        
    case 'contratante_descargar_documento':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $contratoId = isset($_GET['contrato_id']) ? (int)$_GET['contrato_id'] : 0;
        $aspirante_id = isset($_GET['aspirante_id']) ? (int)$_GET['aspirante_id'] : 0;
        $formato = $_GET['formato'] ?? 'docx';
        
        if (!$contratoId) {
            die('Contrato no especificado');
        }
        
        $contratanteController = new ContratanteController();
        $contrato = $contratanteController->obtenerContrato($contratoId, $usuario_actual['cedula']);
        
        if (!$contrato) {
            die('Contrato no encontrado');
        }
        
        // Siempre usar el archivo generado (con campos llenos)
        $archivoGenerado = $contrato['archivo_generado'] ?? null;
        
        if (empty($archivoGenerado) || !file_exists($archivoGenerado)) {
            die('Documento no generado. Debe completar y generar el documento primero.');
        }
        
        $archivo = '';
        $mensajeError = '';
        
        if ($formato === 'pdf') {
            // Intentar convertir a PDF
            $resultado = $contratanteController->generarPDFDesdeDocx($archivoGenerado);
            
            if ($resultado['success']) {
                $archivo = $resultado['ruta'];
            } else {
                // Si no se puede convertir, mostrar mensaje y ofrecer DOCX
                $mensajeError = $resultado['error'] ?? 'No se pudo convertir a PDF';
                
                // Si el usuario realmente quiere PDF pero no se puede, redirigir con mensaje
                // Pero primero intentar descargar DOCX como alternativa
                if (isset($_GET['forzar_pdf']) && $_GET['forzar_pdf'] == '1') {
                    // Si se fuerza PDF pero no se puede, mostrar error
                    header('Location: index.php?action=contratante_perfil_aspirante&aspirante_id=' . $aspirante_id . '&error=' . urlencode($mensajeError . '. Se descargará el archivo DOCX en su lugar.'));
                    exit();
                }
                
                // Por defecto, ofrecer DOCX como alternativa
                $archivo = $archivoGenerado;
                $formato = 'docx';
            }
        } else {
            // Descargar el DOCX generado (con campos llenos)
            $archivo = $archivoGenerado;
        }
        
        if (empty($archivo) || !file_exists($archivo)) {
            die('Archivo no encontrado: ' . htmlspecialchars($archivo));
        }
        
        // Determinar tipo MIME y nombre de archivo
        $extension = pathinfo($archivo, PATHINFO_EXTENSION);
        $nombreArchivo = basename($archivo);
        
        // Si se solicitó PDF pero se está descargando DOCX, cambiar el nombre
        if ($formato === 'docx' && isset($_GET['formato']) && $_GET['formato'] === 'pdf') {
            $nombreArchivo = str_replace('.docx', '.pdf', $nombreArchivo);
            // Agregar nota en el nombre
            $nombreArchivo = str_replace('.pdf', '_convertido_a_docx.docx', $nombreArchivo);
        }
        
        // Configurar headers según el tipo de archivo
        if ($extension === 'pdf') {
            header('Content-Type: application/pdf');
        } else {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        }
        
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Content-Length: ' . filesize($archivo));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Si hay mensaje de error, agregarlo como header (pero no afecta la descarga)
        if (!empty($mensajeError)) {
            header('X-Error-Message: ' . urlencode($mensajeError));
        }
        
        readfile($archivo);
        exit();
        break;
        
    case 'contratante_ver_documento':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        $documento_id = isset($_GET['documento_id']) ? (int)$_GET['documento_id'] : 0;
        $aspirante_id = isset($_GET['aspirante_id']) ? (int)$_GET['aspirante_id'] : 0;
        $empresa_id = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
        
        if (!$documento_id) {
            die('Documento no especificado');
        }
        
        $db = getDBConnection();
        
        // Obtener información del documento
        $sql = "SELECT ad.*, a.cedula as aspirante_cedula
                FROM aspirante_documentos ad
                INNER JOIN aspirantes a ON ad.aspirante_id = a.id
                INNER JOIN contratos c ON c.empresa_id = a.empresa_id
                WHERE ad.id = :documento_id
                AND c.contratante_cedula = :cedula
                LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':documento_id', $documento_id, PDO::PARAM_INT);
        $stmt->bindParam(':cedula', $usuario_actual['cedula'], PDO::PARAM_INT);
        $stmt->execute();
        $documento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$documento) {
            die('Documento no encontrado o no autorizado');
        }
        
        $rutaArchivo = $documento['ruta_archivo'];
        
        // Verificar que el archivo existe
        if (!file_exists($rutaArchivo)) {
            die('El archivo no existe en el servidor');
        }
        
        // Verificar que es PDF
        $extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            die('Este archivo no es un PDF');
        }
        
        // Mostrar el PDF en el navegador (no descargarlo)
        $nombreArchivo = basename($rutaArchivo);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
        header('Content-Length: ' . filesize($rutaArchivo));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        readfile($rutaArchivo);
        exit();
        break;
        
    case 'contratante_subir_documento_aspirante':
        $authController->requerirRol('contratante');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $aspirante_id = isset($_POST['aspirante_id']) ? (int)$_POST['aspirante_id'] : 0;
            $empresa_id = isset($_POST['empresa_id']) ? (int)$_POST['empresa_id'] : 0;
            $tipoDocumento = $_POST['tipo_documento'] ?? 'general';
            $descripcion = $_POST['descripcion'] ?? '';
            $archivo = $_FILES['archivo'] ?? null;
            
            if (!$aspirante_id || !$archivo) {
                header('Location: index.php?action=contratante_perfil_aspirante&aspirante_id=' . $aspirante_id . '&empresa_id=' . $empresa_id . '&error=Parámetros inválidos');
                exit();
            }
            
            $contratanteController = new ContratanteController();
            $resultado = $contratanteController->subirDocumentoAspirante(
                $aspirante_id,
                $archivo,
                $descripcion,
                $tipoDocumento,
                $usuario_actual['cedula']
            );
            
            if ($resultado['success']) {
                header('Location: index.php?action=contratante_perfil_aspirante&aspirante_id=' . $aspirante_id . '&empresa_id=' . $empresa_id . '&success=1');
            } else {
                header('Location: index.php?action=contratante_perfil_aspirante&aspirante_id=' . $aspirante_id . '&empresa_id=' . $empresa_id . '&error=' . urlencode($resultado['error']));
            }
            exit();
        }
        
        header('Location: index.php?action=contratante_aspirante');
        exit();
        break;
        
    // ==================== EMPLEADO ====================
    case 'empleado_dashboard':
        $authController->requerirRol('empleado');
        $usuario_actual = $authController->obtenerUsuarioActual();
        
        echo "<h1>Dashboard Empleado</h1>";
        echo "<p>Bienvenido, " . htmlspecialchars($usuario_actual['nombre_completo']) . "</p>";
        echo "<a href='index.php?action=logout'>Cerrar Sesión</a>";
        break;
        
    default:
        header('Location: index.php?action=login');
        exit();
}
?>
