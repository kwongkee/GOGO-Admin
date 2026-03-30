<?php

define("JAVA_DEBUG", true); //调试设置
define("JAVA_HOSTS", "127.0.0.1:8881");
define("JAVA_LOG_LEVEL", 2);
require_once("Java.inc");
java_require('./XmlDigitalSignatureGenerator.jar');
try {
    $xmlFilePath = getcwd().'/xml/employeesalary.xml';
    $signedXmlFilePath= getcwd().'/xml/digitallysignedEmpSal.xml';
    $privateKeyFilePath =  getcwd().'/keys/privatekey.key';
    $publicKeyFilePath = getcwd().'/keys/publickey.key';
    $xmlSig =new java("com.ddlab.rnd.xml.digsig.XmlDigitalSignatureGenerator");
    $ss= $xmlSig->generateXMLDigitalSignature($xmlFilePath, $signedXmlFilePath, $privateKeyFilePath, $publicKeyFilePath);
    var_dump($ss);
} catch (JavaException $ex) {
    echo "An exception occured: "; echo $ex; echo "<br>\n";
}