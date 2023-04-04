<?php

if (!defined('OK_LOADME')) {
    die('o o p s !');
}

$LANG = array();

// Translation Details
$translation_str = 'Spanish';
$translation_author = 'Holala';
$translation_version = '1.9.0';
$translation_update = '5';
$translation_stamp = '2021-05-28 22:02';

// Character encoding, example: utf-8, iso-8859-1
$LANG['lang_iso'] = "es";
$LANG['lang_charset'] = "iso-8859-1";

// ----------------
// Array of Language
// ----------------
$LANG['g_pagenotfound'] = "¡Página no encontrada!";
$LANG['g_continue'] = "Continuar";
$LANG['g_registration'] = "Registro";
$LANG['g_register'] = "Registrarse";
$LANG['g_agreeterms'] = "Estoy de acuerdo con los términos del sitio";
$LANG['g_termscon'] = "Términos y condiciones";
$LANG['g_haveacc'] = "¿Tiene una cuenta?";
$LANG['g_donothaveacc'] = "¿No tiene una cuenta?";
$LANG['g_createacc'] = "Crear uno";
$LANG['g_forgotpass'] = "Olvidé mi contraseña";
$LANG['g_forgotpassresetlink'] = "Le enviaremos un enlace para restablecer su contraseña";
$LANG['g_resetpass'] = "Restablecer contraseña";
$LANG['g_name'] = "Nombre";
$LANG['g_firstname'] = "Nombre";
$LANG['g_lastname'] = "Apellido";
$LANG['g_email'] = "Correo electrónico";
$LANG['g_username'] = "Nombre de usuario";
$LANG['g_dashboard'] = "Tablero";
$LANG['g_admincp'] = "Admin CP";
$LANG['g_admincpinit'] = "ACP";
$LANG['g_membercp'] = "CP miembro";
$LANG['g_membercpinit'] = "MCP";
$LANG['g_rememberme'] = "Recordarme";
$LANG['g_successlogout'] = "Se ha cerrado la sesión correctamente";
$LANG['g_invalidtoken'] = "Token inválido, ¡inténtalo de nuevo!";
$LANG['g_invalidinput'] = "Formato de entrada no válido, inténtelo de nuevo.";
$LANG['g_dashboardtitle'] = "Tablero";
$LANG['g_accoverview'] = "Descripción general de la cuenta";
$LANG['g_referrallist'] = "Lista de referencias";
$LANG['g_referrals'] = "Referencias";
$LANG['g_historylist'] = "Historial de transacciones";
$LANG['g_withdrawreq'] = "Solicitud de retiro";
$LANG['g_withdrawstr'] = "Solicitud de retiro";
$LANG['g_withdrawfee'] = "Cargo por retiro";
$LANG['g_findreferral'] = "Buscar referencia";
$LANG['g_addreferral'] = "Agregar referencia";
$LANG['g_memberprofile'] = "Perfil de miembro";
$LANG['g_findhistory'] = "Buscar historial";
$LANG['g_point'] = "Punto";
$LANG['g_hits'] = "Golpes";
$LANG['g_earning'] = "Ganar";
$LANG['g_registered'] = "Registrado";
$LANG['g_active'] = "Activo";
$LANG['g_expire'] = "Caduca";
$LANG['g_pending'] = "Pendiente";
$LANG['g_inactive'] = "Inactivo";
$LANG['g_limited'] = "Limitado";
$LANG['g_blocked'] = "Bloqueado";
$LANG['g_refurl'] = "URL de referencia";
$LANG['g_mysponsor'] = "Mi patrocinador";
$LANG['g_recentref'] = "Referencias recientes";
$LANG['g_performance'] = "Rendimiento";
$LANG['g_membership'] = "Membresía";
$LANG['g_transactionid'] = "ID de transacción";
$LANG['g_description'] = "Descripción";
$LANG['g_keyword'] = "Palabra clave";
$LANG['g_balance'] = "Equilibrio";
$LANG['g_account'] = "Cuenta";
$LANG['g_content'] = "Contenido";
$LANG['g_all'] = "Todos";
$LANG['g_activeonly'] = "Solo activo";
$LANG['g_editpayplaninfo'] = "Estas opciones cambiarán los valores tal cual, como funciones respectivas, y no afectarán ningún proceso que se asocie con comisiones, historial de transacciones u otros miembros.";
$LANG['g_withdrawstatusinfo'] = "<blockquote><p><strong>Pending</strong>: La solicitud se ha enviado pero aún no se ha procesado. <strong>Verified</strong>: La solicitud ha pasado la verificación. <strong>Processing</strong>: la solicitud se está procesando. Una vez procesada, los fondos se enviarán a su cuenta. </p> </blockquote> ";
$LANG['g_cookieconsent'] = "Este sitio web solo utiliza cookies que son necesarias para brindar la mejor experiencia.";
$LANG['g_norecordinfo'] = "No se encontró ningún registro";
$LANG['g_norecordgen'] = "Lo sentimos, no podemos encontrar ningún dato";
$LANG['g_norecordgeninfo'] = "Para deshacerse de este mensaje, registre un nuevo miembro y selecciónelo de la lista desplegable anterior.";
$LANG['g_nocontent'] = "No pudimos encontrar ninguna página";
$LANG['g_nocontentinfo'] = "Lo sentimos, no podemos encontrar ningún contenido para usted :(";

$LANG['a_managemember'] = "Administrar miembro";
$LANG['a_findmember'] = "Buscar miembro";
$LANG['a_historylist'] = "Historial de transacciones";
$LANG['a_withdrawlist'] = "Retirar solicitud";
$LANG['a_genealogylist'] = "Genealogía de miembros";
$LANG['a_genealogynote'] = "Debido a la limitación de análisis de HTML del navegador, habilite la Genealogía de miembros cuando se use el plan Matrix y deshabilítelo cuando el sistema usa el plan Uninivel.";
$LANG['a_getstart'] = "Comenzando";
$LANG['a_digifile'] = "Producto digital";
$LANG['a_digicontent'] = "Contenido digital";
$LANG['a_termscon'] = "Términos Condiciones";
$LANG['a_notifylist'] = "Lista de notificaciones";
$LANG['a_settings'] = "Configuración general";
$LANG['a_payplan'] = "Configuración del plan de pago";
$LANG['a_payment'] = "Opciones de pago";
$LANG['a_languagelist'] = "Administrar idioma";
$LANG['a_updates'] = "Mantenimiento";
$LANG['a_outplanmark'] = "El plan de pago del miembro no es lo mismo que el plan de pago del sistema. Podría suceder cuando la configuración del plan de pago se haya cambiado después de que este miembro se haya registrado.";
$LANG['a_outplanreg'] = "La tarifa de registro del miembro no es la misma que la del plan de pago del sistema. Podría suceder cuando la tarifa de registro en la configuración del plan de pago se haya cambiado después de que este miembro se haya registrado.";

$LANG['m_getstarted'] = "Comenzando";
$LANG['m_genealogyview'] = "Genealogía";
$LANG['m_digiload'] = "Descargar";
$LANG['m_digiview'] = "Contenido de la página";
$LANG['m_planpay'] = "Pago";
$LANG['m_planreg'] = "Actualizar";
$LANG['m_profilecfg'] = "Perfil";
$LANG['m_feedback'] = "Comentarios";
$LANG['m_userlist'] = "Referencia";
$LANG['m_historylist'] = "Transacción";
$LANG['m_withdrawreq'] = "Retirar";
$LANG['m_withdrawamount'] = "Cantidad a retirar";
$LANG['m_genealogyview'] = "Genealogía";
$LANG['m_membergenealogy'] = "Genealogía de miembros";
$LANG['m_nofile'] = "No pudimos encontrar ningún archivo";
$LANG['m_nofilenote'] = "Lo sentimos, no podemos encontrar ningún archivo descargable para usted :(";
$LANG['m_withdrawreqnote'] = "¡Se le permite enviar una solicitud de retiro una vez por vez!";
$LANG['m_clicklefttocnt'] = "¡Haga clic en el menú de la página a la izquierda para mostrar el contenido!";
$LANG['m_profileaccnote'] = "Complete los siguientes formularios, asegúrese de que el valor que ingresó sea válido.";
$LANG['m_profilepaynote'] = "Configuración de la cuenta de miembro";
$LANG['m_profilewebnote'] = "Ingrese los detalles de su sitio web a continuación (opcional)";
$LANG['m_profilepassnote'] = "Actualice su contraseña utilizando los formularios a continuación. Deje en blanco para mantener la contraseña actual.";
$LANG['m_confirmpass'] = "Confirmar mi cambio de contraseña";
$LANG['m_feedbacknote'] = "Use el siguiente formulario para cualquier pregunta, solicitud de soporte o sugerencia de función";
$LANG['m_payoption'] = "Opción de pago";
$LANG['m_payinfo'] = "Complete su pago haciendo clic en el botón Realizar pago en la opción de pago disponible a continuación.";
$LANG['m_testpayinfo'] = "¡Haga clic en el botón de abajo para simular el proceso de pago!";
$LANG['m_notice'] = "¡Aviso!";
$LANG['m_noticereg'] = "No está registrado en";
$LANG['m_noticepay'] = "Su cuenta no está activa, complete el pago";
$LANG['m_noticerepay'] = "Tiene un pago pendiente, complete el pago";
$LANG['m_ipnthanks'] = "Gracias";
$LANG['m_ipnthanksverify'] = "Espere unos momentos para verificar su pago";
$LANG['m_ipnnextbtn'] = "Continuar";
$LANG['m_ibconversion'] = "Conversión";
$LANG['m_ibpersonal'] = "Personal";
$LANG['m_ibwallet'] = "Monedero";
$LANG['m_registeredsince'] = "Registrado desde";

//---
$LANG['g_passmeter'] = "La contraseña debe incluir al menos una letra mayúscula y minúscula, un número, un carácter especial y debe tener al menos 8 caracteres de longitud.";
$LANG['g_noregister'] = "¡Debido al mantenimiento del sistema, actualmente no aceptamos nuevos registros!";
$LANG['g_noreferrer'] = "¡No puede registrarse sin un referente válido!";
$LANG['g_toastsuccess'] = "Éxito";
$LANG['g_toastsuccessinfo'] = "¡Registro procesado correctamente!";
$LANG['g_toastfail'] = "Advertencia";
$LANG['g_toastfailinfo'] = "Error de registro. ¡Inténtelo de nuevo!";
$LANG['g_withdrawispaid'] = "Procesando";
$LANG['g_withdrawislook'] = "Verificado";
$LANG['g_withdrawiswait'] = "Pendiente";
$LANG['a_isdeflang'] = "Establecer como idioma predeterminado";
