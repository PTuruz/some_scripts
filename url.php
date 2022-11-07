<?php 
//The script provide all URLs from given url per line or json format
//Example 1 url.php?u=https://google.com&o=stdout
//Example 2 url.php?u[]=https://google.com&u[]=https://google.org&o=json

//Check input(GET) parameters exist 
if(isset($_GET["u"]) && isset($_GET["o"])){

    //Check if many URLs
    if(!is_array($_GET["u"])){
        $urls[0]=$_GET["u"];
    }
    else{
        $urls=$_GET["u"];
    }

    for($j=0;$j < count($urls) ; $j++){

            //Check u parameter validation 
        if (filter_var($urls[$j], FILTER_VALIDATE_URL) !== FALSE){

            //Check o parameter validation 
            if ($_GET["o"]=='stdout' || $_GET["o"]=='json'){
                $html = file_get_contents($urls[$j]);
                
                //Regex for get href content
                $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";

                //Get href and add it to $matches array
                preg_match_all("/$regexp/siU", $html, $matches, PREG_SET_ORDER);

                //Check o parameter for print data
                if($_GET["o"]=='stdout'){
                    foreach($matches as $url) {

                        //If URL start with '/' then add domain for full URL
                        if(!str_starts_with($url[2], 'http')){
                            $url[2]=rtrim($urls[$j], '/').$url[2] ;
                        }
                    //Output data per line   
                    echo $url[2]."</br>";
                    }
                }
                else{//json output
                    for($i=0;$i< count($matches);$i++){
                        $parse_url = parse_url($matches[$i][2]);
                        //If URL start with '/' then get domain
                        if($parse_url['host']==null){
                            $parse_url = parse_url($urls[$j]);
                        }
                        $hostname=$parse_url['scheme'].'://'.$parse_url['host'];
                        $path='/'.trim($matches[$i][2], $hostname);

                        //Chek if array(hostname alredy) exist and add paths
                        if(isset($json_data[$hostname])){
                            $json_data[$hostname][count($json_data[$hostname])]=$path;
                        }
                        else{
                            $json_data[$hostname][0]=$path;
                        }
                    }
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($json_data,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                }
                
            }
            else {
                echo "The 'o' parameters can have only values 'stdout' or 'json'";
            }
        }
        else {
            echo "The 'u' parameter mast be valid URL";
        }
    }
}
else {
    echo "Please give 'u' and 'o' GET parameters";
}
?>