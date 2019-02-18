<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");
class ProfileService{
    //public var $appname="profileapp";
    public function postInvoiceSave($req,$res){
        
        $Transaction=$req->Body(true);
        $user= Auth::Autendicate("profile","postInvoiceSave",$res);
        if(!isset($Transaction->email)){
            $res->SetError ("provide email");
            
        }
        if(!isset($Transaction->contactno)){
            $res->SetError ("provide contact no");
        }
        
        $result = SOSSData::Query ("profile", urlencode("id:".$Transaction->profileId.""));
        
        //return $result;
        if(count($result->result)!=0)
        {
            $Transaction->preparedByID=$user->userid;
            $Transaction->preparedBy=$user->email;
            $Transaction->PaymentComplete="N";
            $Transaction->balance=$Transaction->total;
            $result = SOSSData::Insert ("orderheader", $Transaction,$tenantId = null);
            CacheData::clearObjects("orderheader");
            if($result->success){
                $Transaction->invoiceNo = $result->result->generatedId;
                $ledgertran =new StdClass;
                $ledgertran->profileid=$Transaction->profileId;
                $ledgertran->tranid=$Transaction->invoiceNo;
                $ledgertran->trantype='invoice';
                $ledgertran->tranDate=$Transaction->invoiceDate;
                $ledgertran->description='Invoice No Has been generated';
                $ledgertran->amount=$Transaction->total;
                $result=SOSSData::Insert ("ledger", $ledgertran,$tenantId = null);
                CacheData::clearObjects("ledger");
                //return $Transaction;
                if($result->success){
                
                    $profileservices=array();
                    foreach($Transaction->InvoiceItems as $key=>$value){
                        $Transaction->InvoiceItems[$key]->invoiceNo=$Transaction->invoiceNo;
                        if(strtolower($value->invType)=="service"){
                            $serviceitems =new StdClass;
                            $serviceitems->invid=$Transaction->invoiceNo;
                            $serviceitems->profileId=$Transaction->profileId;
                            $serviceitems->itemid=$value->itemid;
                            $serviceitems->name=$value->name;
                            $serviceitems->purchaseddate=$Transaction->invoiceDate;
                            $serviceitems->price=$value->total;
                            $serviceitems->catogory=$value->catogory;
                            $serviceitems->uom=$value->uom;
                            $serviceitems->qty=$value->qty;
                            $serviceitems->status="ToBeActive";
                            
                            array_push($profileservices,$serviceitems);
                        }
                        //var_dump($Transaction->InvoiceItems[$key]->invoiceNo);
                    }
                    //return $profileservices;
                    $result = SOSSData::Insert ("orderdetails", $Transaction->InvoiceItems,$tenantId = null);
                    if(count($profileservices)!=0){
                        $result = SOSSData::Insert ("profileservices", $profileservices,$tenantId = null);
                        CacheData::clearObjects("profileservices");
                    }
                    //return $result;
                    
                    CacheData::clearObjects("orderdetails");
                }else{
                    $res->SetError ("Erorr");
                    return $result;
                }
                //unset($value); 
                $result = SOSSData::Query ("profilestatus", urlencode("profileid:".$Transaction->profileId.""));
                CacheData::clearObjects("profilestatus");
                //$status=null;
                if(count($result->result)!=0){
                    $status= $result->result[0];
                    $status->outstanding+=$Transaction->total;
                    $status->totalInvoicedAmount+=$Transaction->total;
                    $status->totalPaidAmout+=0;
                    $result=SOSSData::Update ("profilestatus", $status,$tenantId = null);
                }else{
                    $status=new stdClass();
                    $status->profileid=$Transaction->profileId;
                    $status->outstanding=$Transaction->total;
                    $status->totalInvoicedAmount=$Transaction->total;
                    $status->totalPaidAmout=0;
                    $result=SOSSData::Insert ("profilestatus", $status,$tenantId = null);
                    
                }
                
                return $Transaction;
            }else{
                return $result;
            }
        }else{
           //var_dump($result->response[0]->id);
           //exit();
           $res->SetError ("Invalied Profile");
           exit();
        }
        
        
    }

    public function postPOSave($req,$res){
        
        $Transaction=$req->Body(true);
        $user= Auth::Autendicate("profile","postPOSave",$res);
        if(!isset($Transaction->email)){
            $res->SetError ("provide email");
            
        }
        if(!isset($Transaction->contactno)){
            $res->SetError ("provide contact no");
        }
        
        $result = SOSSData::Query ("profile", urlencode("id:".$Transaction->profileId.""));
        
        //return $result;
        if(count($result->result)!=0)
        {
            $Transaction->preparedByID=$user->userid;
            $Transaction->preparedBy=$user->email;
            $Transaction->Complete="N";
            $Transaction->balance=$Transaction->total;
            $result = SOSSData::Insert ("poheader", $Transaction,$tenantId = null);
            CacheData::clearObjects("poheader");
            if($result->success){
                $Transaction->tranNo = $result->result->generatedId;
                if($result->success){
                    
                    $profileservices=array();
                    foreach($Transaction->InvoiceItems as $key=>$value){
                        $Transaction->InvoiceItems[$key]->tranNo=$Transaction->tranNo;
                        //var_dump($Transaction->InvoiceItems[$key]->tranNo);
                    }
                    $result = SOSSData::Insert ("podetails", $Transaction->InvoiceItems,$tenantId = null);
                    
                    CacheData::clearObjects("podetails");
                }else{
                    $res->SetError ("Erorr");
                    return $result;
                }
                
                return $Transaction;
            }else{
                return $result;
            }
        }else{
           //var_dump($result->response[0]->id);
           //exit();
           $res->SetError ("Invalied Profile");
           exit();
        }
        
        
    }

    public function postGRNSave($req,$res){
        
        $Transaction=$req->Body(true);
        $user= Auth::Autendicate("profile","postGRNSave",$res);
        
        if(!isset($Transaction->poid)){
            $res->SetError ("PO is not corrrect");
            return;
        }
        $result = SOSSData::Query ("poheader", urlencode("tranNo:".$Transaction->poid.""));
        
        //return $result;
        if(count($result->result)!=0)
        {
            $PO =$result->result[0];
            if($PO->Complete=='Y'){
                $res->SetError ("GRN Already Generated for this PO");
                return;
            }
            $Transaction->preparedByID=$user->userid;
            $Transaction->preparedBy=$user->email;
            $Transaction->Complete="N";
            $Transaction->balance=$Transaction->total;
            
            $result = SOSSData::Insert ("grnheader", $Transaction,$tenantId = null);
            CacheData::clearObjects("grnheader");
            if($result->success){
                $Transaction->tranNo = $result->result->generatedId;
                $ledgertran =new StdClass;
                $ledgertran->profileid=$Transaction->profileId;
                $ledgertran->tranid=$Transaction->tranNo;
                $ledgertran->trantype='GRN';
                $ledgertran->tranDate=$Transaction->tranDate;
                $ledgertran->description='Invoice No Has been generated';
                $ledgertran->amount=-1*$Transaction->total;
                $result=SOSSData::Insert ("ledger", $ledgertran,$tenantId = null);
                if($result->success){
                    
                    $profileservices=array();
                    foreach($Transaction->InvoiceItems as $key=>$value){
                        $Transaction->InvoiceItems[$key]->tranNo=$Transaction->tranNo;
                        //var_dump($Transaction->InvoiceItems[$key]->tranNo);
                    }
                    $result = SOSSData::Insert ("grndetails", $Transaction->InvoiceItems,$tenantId = null);
                    $PO->Complete='Y';
                    $result=SOSSData::Update ("poheader", $PO,$tenantId = null);
                    CacheData::clearObjects("grndetails");
                }else{
                    $res->SetError ("Erorr");
                    return $result;
                }
                
                return $Transaction;
            }else{
                return $result;
            }
        }else{
           //var_dump($result->response[0]->id);
           //exit();
           $res->SetError ("Invalied PO");
           exit();
        }
        
        
    }

    public function postPaymentSave($req,$res){
        $payment=$req->Body(true);
        $user= Auth::Autendicate("profile","postPaymentSave",$res);
        if(!isset($payment->email)){
            $res->SetError ("provide email");
            
        }
        if(!isset($payment->contactno)){
            $res->SetError ("provide contact no");
        }
        
        $result = SOSSData::Query ("profile", urlencode("id:".$payment->profileId.""));
        $payment->collectedByID=$user->userid;
        $payment->collectedBy=$user->email;
        //return $result;
        if(count($result->result)!=0)
        {
            
            $result = SOSSData::Insert ("paymentheader", $payment,$tenantId = null);
            CacheData::clearObjects("paymentheader");
           
            if($result->success){
                $payment->receiptNo = $result->result->generatedId;
                $ledgertran =new StdClass;
                $ledgertran->profileid=$payment->profileId;
                $ledgertran->tranid=$payment->receiptNo;
                $ledgertran->trantype='receipt';
                $ledgertran->tranDate=$payment->receiptDate;
                $ledgertran->description='Invoice No Has been generated';
                $ledgertran->amount=-1*$payment->paymentAmount;
                $result=SOSSData::Insert ("ledger", $ledgertran,$tenantId = null);
                //return $payment;
                CacheData::clearObjects("ledger");
                if($result->success){
                    $balance=$payment->paymentAmount;
                    $invUpdate=array();
                    foreach($payment->InvoiceItems as &$value){
                        $value->receiptNo=$payment->receiptNo;
                        $paymentComplete='N';
                        if($balance!=0){
                            if($balance>=$value->DueAmount){
                                $value->PaidAmout=$value->DueAmount;
                                $balance-=$value->DueAmount;
                                $value->Balance=0;
                                $paymentComplete='Y';
                            }else{
                                $value->PaidAmout=$balance;
                                $value->Balance=$value->DueAmount-$balance;
                                $balance=0;
                            }
                            $invDetails=new stdClass();
                            $invDetails->invoiceNo=$value->transactionid;
                            $invDetails->paidamount=$value->PaidAmout;
                            $invDetails->balance=$value->Balance;
                            $invDetails->PaymentComplete=$paymentComplete;
                            $result=SOSSData::Update ("orderheader", $invDetails,$tenantId = null);
                            array_push($invUpdate,$invDetails);
                        }
                    }
                    //return $invUpdate;
                    $result = SOSSData::Insert ("paymentdetails", $payment->InvoiceItems,$tenantId = null);
                    CacheData::clearObjects("paymentdetails");
                    CacheData::clearObjects("orderheader");
                    //return $result;
                }else{
                    $res->SetError ("Erorr");
                    return $result;
                }
                unset($value); 
                $result = SOSSData::Query ("profilestatus", urlencode("profileid:".$payment->profileId.""));
                CacheData::clearObjects("profilestatus");
                //$status=null;
                if(count($result->result)!=0){
                    $status= $result->result[0];
                    $status->outstanding-=$payment->paymentAmount;
                    $status->totalInvoicedAmount+=0;
                    $status->totalPaidAmout+=$payment->paymentAmount;
                    $result=SOSSData::Update ("profilestatus", $status,$tenantId = null);
                }else{
                    $status=new stdClass();
                    $status->profileid=$payment->profileId;
                    $status->outstanding=-1*$payment->paymentAmount;
                    $status->totalInvoicedAmount=$payment->paymentAmount;
                    $status->totalPaidAmout=$payment->paymentAmout;
                    $result=SOSSData::Insert ("profilestatus", $status,$tenantId = null);
                    
                }
                
                return $payment;
            }else{
                return $result;
            }
        }else{
           //var_dump($result->response[0]->id);
           //exit();
           $res->SetError ("Invalied Profile");
           exit();
        }
        
        
    }

    public function postSave($req,$res){
        $profile=$req->Body(true);
        $user= Auth::Autendicate("profile","postSave",$res);
        if(!isset($profile->email)){
            //http_response_code(500);
            $res->SetError ("provide email");
            
        }
        if(!isset($profile->contactno)){
            //http_response_code(500);
            $res->SetError ("provide contact no");
            
        }
        //var_dump($profile);
        //exit();
        $result = SOSSData::Query ("profile", urlencode("email:".$profile->email.""));
        
        //return $result;
        if(count($result->result)==0)
        {
            $profile->createdate=date_format(new DateTime(), 'm-d-Y H:i:s');
            $profile->userid=$user->userid;
            $profile->status="tobeactivated";
            $result = SOSSData::Insert ("profile", $profile,$tenantId = null);
            CacheData::clearObjects("profile");
            return $result;
        }else{
           if($profile->id!=$result->result[0]->id){
            $res->SetError ("Profile registered with this email");
           }else{
            $result = SOSSData::Update("profile", $profile,$tenantId = null);
            CacheData::clearObjects("profile");
            return $result;
           }
        }
        
        
    }

    public function getSearch($req){
        $s  =null;
        if(isset($_GET["q"])){
            $search  =$_GET["q"];
        }
        $result= CacheData::getObjects(md5($search),"profile");
        if(!isset($result)){
            $result = SOSSData::Query ("profile",urlencode($search));
            if($result->success){
                if(isset($result->result)){
                    CacheData::setObjects(md5($search),"profile",$result->result);
                }
            }
            return $result->result;
        }else{
            return $result;
        }
    }

    public function postq($req){
        $sall=$req->Body(true);
        $f=new stdClass();
        foreach($sall as $s){
            $result= CacheData::getObjects(md5($s->search),$s->storename);
            if(!isset($result)){
                $result = SOSSData::Query ($s->storename,urlencode($s->search));
                if($result->success){
                    $f->{$s->storename}=$result->result;
                    if(isset($result->result)){
                        CacheData::setObjects(md5($s->search),$s->storename,$result->result);
                    }
                }else{
                    $f->{$s->storename}=null;
                }
            }else{
                $f->{$s->storename}= $result;
            }
            
        }
        return $f;
    }
    
}

?>