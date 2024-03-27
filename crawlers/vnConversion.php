<?php
    $dbhost="localhost";
    $dbname="doomavideo";
    $dbusername="root";
    $dbpassword="";
    
    $db=new mysqli($dbhost, $dbusername, $dbpassword, $dbname);
    
    $result = $db->query('Select videoid, title from video');
    
    $str='';
    $titles=array();
    $videoids=array();
    while ($row = $result->fetch_assoc())
    {
        $titles[]=$row["title"];
        $videoids[]=$row["videoid"];
    }
    
    $str= implode('[%;%]',$titles);
    $soapClient = new SoapClient("http://www.enderminh.com/webservices/VietnameseConversions.asmx?WSDL"); 
    
    $ap_param = array('message'     =>    $str); 
    $info = $soapClient->__call("UnicodeHTMLToUnicode", array($ap_param)); 
    
    $ap_param = array('message'     =>    $info->UnicodeHTMLToUnicodeResult); 
    $info = $soapClient->__call("UnicodeToASCII", array($ap_param));  
    
    $titles=explode('[%;%]',$info->UnicodeToASCIIResult);
    
    if(count($titles)==count($videoids))
    {
        for($i=0;$i<count($titles);$i++)
        {
            printf ("update video set title_ascii = '%s' where videoid = '%s';\n", mysqli_real_escape_string($db,$titles[$i]), $videoids[$i]);
        }
    }
?>