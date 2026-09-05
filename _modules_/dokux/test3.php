<?php

$ocrtext_wsw = 'HOSPITAL UNIVERSITARIO LOS ARCOS DEL MAR MENOR<br />Servico Paraje Torre Octavio sín Pozo Aledo<br />Murciano 30739 San Javier<br />de Salud »<br />(Murcia)<br />Tel.: 968 56 50 00/10 AREA DE SALUD VI!<br />Fax: 968 56 50 33 MAR MENOR<br />San Javier, 10-Marzo-2021<br />DE: Dra Elena Fontes Manzano. Médico de Admisión y Documentación Clínica<br />A: Centro Salud Puerta Nueva (Zamora)<br />ec”<br />ASUNTO: Solicitud dé Historia Clínica —)<br />Adjunto remito solicitud de la Historia Clínica de Atención Primaria, de la paciente<br /> <br />Burcarmos<br />D* M? Concepción Miñambres Prieto (DNI 70993526R) recibida del Consultorio de<br />Santiago de la Ribera (Murcia), por cambio de domicilio.<br />Atentamente,<br />Fdo.: Dra. Elena Fontes Manzano<br />Médico de Admisión y Documentación Clínica<br /><br />';

Vars::debug_var($ocrtext_wsw);
/*        
        $doc_type=19;
        $filetypes = Table::sqlQuery('SELECT ID,NAME,EXP FROM DOKU_FILETYPES ORDER BY PRIORITY');
        foreach($filetypes as $type){
Vars::debug_var($type);
            $doc_type = preg_match_all($type['EXP'], $ocrtext_wsw); // Devuelve 1
            if($doc_type==1) break;
        }


*/

$type['EXP']='/HISTORIA(\s*)CL(í|i)NICA/i';
$doc_type = preg_match_all($type['EXP'], $ocrtext_wsw); // Devuelve 1
Vars::debug_var($doc_type);
