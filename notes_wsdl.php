<?php
/**
 *
 * @author Andres Duarte M.
 *
 */
require_once 'lib/nusoap.php';

ini_set("soap.wsdl_cache_enabled", "0"); // deshabilitando cache WSDL

$server = new soap_server();
$server->configureWSDL('wsNotes', 'urn:wsNotes');
$server->wsdl->schemaTargetNamespace = 'urn:wsNotes';

$server->soap_defencoding = 'UTF-8';
$server->decode_utf8 = FALSE;
$server->encode_utf8 = TRUE;

$server->wsdl->addComplexType(
    'Item',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'id' => array('name' => 'id', 'type' => 'xsd:int'),
        'title'=> array('name' => 'title', 'type' => 'xsd:string'),
        'body'=> array('name' => 'body', 'type' => 'xsd:string'),
        'created'=> array('name' => 'created', 'type' => 'xsd:string'),
        'last_update' => array('name' => 'last_update', 'type' => 'xsd:string')
    )
);

$server->wsdl->addComplexType(
    'List',
    'complexType',
    'array',
    '',
    '',
    array(
        'Item' => array('name' => 'Item', 'type' => 'tns:Item[]')
    )
);

$server->register('read',
    array(  // request
        'ws_user' => 'xsd:string',     // obligatorio
        'ws_pass' => 'xsd:string',     // obligatorio
        'id'      => 'xsd:int'         // opcional
    ),
    array(  // response
        'Success' => 'xsd:boolean',
        'Message' => 'xsd:string',
        'Rows'    => 'xsd:int',
        'List'    => 'tns:List'
    ),
    'urn:wsNotes',                   // namespace                      
    'urn:wsNotes#List',              // accion SOAP
    'rpc',                           // estilo
    'encoded',                       // tipo de uso                                                  
    'Obtiene las notas registradas'  // documentacion
);

$server->register('create',
    array(  // request
        'ws_user'   => 'xsd:string',    // obligatorio
        'ws_pass'   => 'xsd:string',    // obligatorio
        'title'     => 'xsd:string',    // obligatorio
        'body'      => 'xsd:string'     // obligatorio
    ),
    array(  // response
        'Success'   => 'xsd:boolean',
        'Message'   => 'xsd:string'
    ),
    'urn:wsNotes',                  // namespace
    '',                             // accion SOAP
    '',                             // estilo
    '',                             // tipo de uso
    'Crea una nota'                 // documentacion
);

$server->register('update',
    array(  // request
        'ws_user'   => 'xsd:string',    // obligatorio
        'ws_pass'   => 'xsd:string',    // obligatorio
        'id'        => 'xsd:int',       // obligatorio
        'title'     => 'xsd:string',    // obligatorio
        'body'      => 'xsd:string'     // obligatorio
    ),
    array(  // response
        'Success'   => 'xsd:boolean',
        'Message'   => 'xsd:string'
    ),
    'urn:wsNotes',                  // namespace
    '',                             // accion SOAP
    '',                             // estilo
    '',                             // tipo de uso
    'Actualiza una nota'            // documentacion
);

$server->register('delete',
    array(  // request
        'ws_user'   => 'xsd:string',    // obligatorio
        'ws_pass'   => 'xsd:string',    // obligatorio
        'id'        => 'xsd:int'        // obligatorio
    ),
    array(  // response
        'Success'   => 'xsd:boolean',
        'Message'   => 'xsd:string'
    ),
    'urn:wsNotes',                  // namespace
    '',                             // accion SOAP
    '',                             // estilo
    '',                             // tipo de uso
    'Elimina una nota'              // documentacion
);

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';        
$server->service($HTTP_RAW_POST_DATA);
?>