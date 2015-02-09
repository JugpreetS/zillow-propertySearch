<?php
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    if(!empty($_GET)){
        
        $streetInput; $cityInput; $stateInput;
        if(isset($_GET["streetInput"]) && !empty($_GET["streetInput"])
           && isset($_GET["cityInput"]) && !empty($_GET["cityInput"])
           && isset($_GET["stateInput"]) && !empty($_GET["stateInput"])){
            
            $streetInput = $_GET["streetInput"];
            $cityInput = $_GET["cityInput"];
            $stateInput = $_GET["stateInput"];        
            
            $zwsid = "X1-ZWz1b39sypbymj_963mm";
            $url = "http://www.zillow.com/webservice/GetDeepSearchResults.htm?zws-id=";        
            $streetInput = str_replace(' ', '+', $streetInput);
            $cityInput = str_replace(' ', '+', $cityInput);
            $url.=$zwsid;
            $url.="&address=";
            $url.=$streetInput;
            $url.="&citystatezip=";
            $url.=$cityInput;
            $url.="%2C+";
            $url.=$stateInput;
            $url.="&rentzestimate=true";
            //$url = "http://www.zillow.com/webservice/GetDeepSearchResults.htm?zws-id=X1-ZWz1b39sypbymj_963mm&address=2636+Menlo+Avenue&citystatezip=los+angeles%2C+CA&rentzestimate=true";
            $response = processAPICall($url);
            //echo 'process API';
            //echo $url;
            $responseCode = (string)$response->message->code;
            if($responseCode==0){                
                $data = buildJSON($response);
                header('Content-type: application/json');
                //echo 'encode start'
                $jObject = json_encode($data);
                
                echo $jObject;
            }
            else{
                //can return a json with error code
                errorMessageNoDataFromAPI();
            }
        }
        else{
            //can return a json with error code
            errorMessageFields();
        }  
        
    }
    function errorMessageFields(){
        $message = array(
          'errorMessage'=>'Missing/Improper field values',
          'errorCode'=> 1
        );
        $result = json_encode($message);
        echo $result;
    }
    
    function errorMessageNoDataFromAPI(){
        $message = array(
          'errorMessage'=>'No data from API for the selected fields',
          'errorCode'=> 2
        );
        $result = json_encode($message);
        echo $result;
    }
    
    function processAPICall($url){
        $data = simplexml_load_string(file_get_contents($url));
        return $data;
    }
    
    function buildJSON($response){
        //echo 'building JSON';
        date_default_timezone_set('America/Los_Angeles');
        $result = $response->response->results->result;
        $result = $result[0];
        $zestimate = $result->zestimate;
        $rentzestimate = $result->rentzestimate;
        $zpid=(string)$result->zpid;
        $homedetails = (string)$result->links->homedetails;
        $street = (string)$result->address->street;
        $city = (string)$result->address->city;
        $state = (string)$result->address->state;
        $zipcode = (string)$result->address->zipcode;
        $latitude = (string)$result->address->latitude;
        $longitude = (string)$result->address->longitude;
        $useCode = (string)$result->useCode;
        if($useCode=="")
            $useCode="";
        
        $lastSoldPrice = (string)$result->lastSoldPrice;
        if($lastSoldPrice=="")
            $lastSoldPrice="";
        else
            $lastSoldPrice = number_format((string)$result->lastSoldPrice,2);
            
        $yearBuilt = (string)$result->yearBuilt;                
        if($yearBuilt=="")
            $yearBuilt="";
        
        $lastSoldDate = (string)$result->lastSoldDate;
        if($lastSoldDate=="")
            $lastSoldDate="";
        else
            $lastSoldDate = (string)date("d-M-Y", strtotime($result->lastSoldDate));
            
        $lotSizeSqFt = (string)$result->lotSizeSqFt;                
        if($lotSizeSqFt=="")
            $lotSizeSqFt="";
        else
            $lotSizeSqFt = (string)$result->lotSizeSqFt;  
        
        
        $estimateLastUpdate = (string)$zestimate->{'last-updated'};
        if($estimateLastUpdate=="")
            $estimateLastUpdate = "";
        else
            $estimateLastUpdate = (string)date("d-M-Y", strtotime($zestimate->{'last-updated'}));
        
        $estimateAmount = (string)$zestimate->amount;
        if($estimateAmount=="")
            $estimateAmount="";
        else
            $estimateAmount= (string)number_format((string)$zestimate->amount,2);
            
            
        $finishedSqFt = (string)$result->finishedSqFt;                
        if($finishedSqFt=="")
            $finishedSqFt="";
        else
            $finishedSqFt = (string)$result->finishedSqFt;
            
        $estimateValueChange = (string)$zestimate->valueChange;
        $estimateValueChangeSign="";
        if($estimateValueChange==""){
            $estimateValueChange="";
        }
        else{
            if(intval($estimateValueChange)< 0){
                $estimateValueChangeSign = "-";
            }
            else{
                $estimateValueChangeSign = "+";
            }
            $estimateValueChange = abs($estimateValueChange);
            $estimateValueChange = number_format($estimateValueChange,2);
        }
        
        
        $imagen = "http://www-scf.usc.edu/~csci571/2014Spring/hw6/down_r.gif";
        $imagep = "http://www-scf.usc.edu/~csci571/2014Spring/hw6/up_g.gif";
        //$down = "http://cs-server.usc.edu:45678/hw/hw6/down_r.gif";
        //$up = "http://cs-server.usc.edu:45678/hw/hw6/up_g.gif";
        
        
        $bathrooms = (string)$result->bathrooms;
        if($bathrooms=="")
            $bathrooms="";
            
        
        if(!isset($zestimate->valuationRange))
            $estimateValuationRangeLow = "";
        else{
            $estimateValuationRangeLow = (string)$zestimate->valuationRange->low;
            if($estimateValuationRangeLow=="")
                $estimateValuationRangeLow = "";
            else{    
                $estimateValuationRangeLow = number_format((string)$zestimate->valuationRange->low,2);
            }
        }  
            
        if(!isset($zestimate->valuationRange))
            $estimateValuationRangeHigh = "";
        else{
            $estimateValuationRangeHigh = (string)$zestimate->valuationRange->high;
            if($estimateValuationRangeHigh=="")
                $estimateValuationRangeHigh = "";
            else{
                $estimateValuationRangeHigh = number_format((string)$zestimate->valuationRange->high,2);
            }
        }
        
        $bedrooms = (string)$result->bedrooms;
        if($bedrooms=="")
            $bedrooms="";
        
        $restimateLastUpdate = (string)$rentzestimate->{'last-updated'};
        if($restimateLastUpdate=="")
            $restimateLastUpdate = "-";
        else
            $restimateLastUpdate = (string)date("d-M-Y", strtotime($rentzestimate->{'last-updated'}));
        
        
        $restimateAmount = (string)$rentzestimate->amount;
        if($restimateAmount=="")
            $restimateAmount = "";
        else
            $restimateAmount = number_format((string)$rentzestimate->amount,2);
        
        
        $taxAssessmentYear = (string)$result->taxAssessmentYear;
        if($taxAssessmentYear=="")
            $taxAssessmentYear = "";
        
        $restimateValueChange = (string)$rentzestimate->valueChange;
        $restimateValueChangeSign="";        
        if($restimateValueChange==""){
            $restimateValueChange = "";
        }
        else{
            if(intval($restimateValueChange)< 0){
                $restimateValueChangeSign = "-";
            }
            else{
                $restimateValueChangeSign = "+";
            }
            $restimateValueChange = abs($restimateValueChange);
            $restimateValueChange = number_format($restimateValueChange,2);
        }
        
        $taxAssesment = (string)$result->taxAssessment;
        if($taxAssesment=="")
            $taxAssesment="";
        else
            $taxAssesment=number_format((string)$result->taxAssessment,2);
        
        if(!isset($rentzestimate->valuationRange))
            $restimateValuationRangeLow  = "";
        else{
            $restimateValuationRangeLow = (string)$rentzestimate->valuationRange->low;
            if($restimateValuationRangeLow=="")
                $restimateValuationRangeLow  = "";
            else{
                $restimateValuationRangeLow = number_format((string)$rentzestimate->valuationRange->low,2);
            }
        }
        
        if(!isset($rentzestimate->valuationRange))
            $restimateValuationRangeHigh="";
        else{
            $restimateValuationRangeHigh = (string)$rentzestimate->valuationRange->high;
            if($restimateValuationRangeHigh=="")
                $restimateValuationRangeHigh="-";
            else{   
                $restimateValuationRangeHigh = number_format((string)$rentzestimate->valuationRange->high,2);
            }
        }
        $data = array(
            'result' => array(
                'homedetails' => $homedetails,
                'street' => $street,
                'city' => $city,
                'state' => $state,
                'zipcode' => $zipcode,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'useCode' => $useCode,
                'lastSoldPrice' => $lastSoldPrice,
                'yearBuilt' => $yearBuilt,
                'lastSoldDate' => $lastSoldDate,
                'lotSizeSqFt' => $lotSizeSqFt,
                'estimateLastUpdate' => $estimateLastUpdate,
                'estimateAmount' => $estimateAmount,
                'finishedSqFt' => $finishedSqFt,
                'estimateValueChangeSign' => $estimateValueChangeSign,
                'imgn' => $imagen,
                'imgp' => $imagep,
                'estimateValueChange' => $estimateValueChange,
                'bathrooms' => $bathrooms,
                'estimateValuationRangeLow' => $estimateValuationRangeLow,
                'estimateValuationRangeHigh' => $estimateValuationRangeHigh,
                'bedrooms' => $bedrooms,
                'restimateLastUpdate' => $restimateLastUpdate,
                'restimateAmount' => $restimateAmount,
                'taxAssessmentYear' => $taxAssessmentYear,
                'restimateValueChangeSign' => $restimateValueChangeSign,
                'restimateValueChange' => $restimateValueChange,
                'taxAssessment' => $taxAssesment,
                'restimateValuationRangeLow' => $restimateValuationRangeLow,
                'restimateValuationRangeHigh' => $restimateValuationRangeHigh
            ),
            'chart' =>array(
                '1year' => array(
                    'url'=> 'http://www.zillow.com/app?chartDuration=1year&chartType=partner&height=300&page=webservice%2FGetChart&service=chart&showPercent=true&width=600&zpid='.$zpid
                ),
                '5year' => array(
                    'url'=> 'http://www.zillow.com/app?chartDuration=5year&chartType=partner&height=300&page=webservice%2FGetChart&service=chart&showPercent=true&width=600&zpid='.$zpid
                ),
                '10year' => array(
                    'url'=> 'http://www.zillow.com/app?chartDuration=10year&chartType=partner&height=300&page=webservice%2FGetChart&service=chart&showPercent=true&width=600&zpid='.$zpid
                )
            )
        );
        //echo 'JOSN built';
        return $data;
    }



?>