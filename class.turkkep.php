<?php
	date_default_timezone_set('Europe/Istanbul');
	class TurkKepEFatura extends SoapClient
	{
		//Türk Kep WSDL Url
		private $WSDLUrl									= null;
		
		//Türk Kep Kullanıcı Adı
		private $kullaniciAdi 								= null;
		
		//Türk Kep Kullanıcı Adı
		private $kullaniciSifresi							= null;
		
		//Faturayı Gönder
		private $NotificationSend							= true;
		
		//Taslak Fatura için true, direk gönderim için false 
		private $isDraft									= false;
		
		//Fatura No 
		private $InvoiceReference							= null;
		
		//Fatura Tipi 
		private $InvoiceType								= "SATIS";
		
		//Fatura Profili 
		private $Profile									= "TEMELFATURA";
		
		//Türk Kep Oturum Token
		private $token 										= null;
		
		//KDV Oran
		private $kdv 										= 18;
		
		//Ürün Para Birimi
		private $ItemCurrency 								= "TurkishLira";
		
		//Fatura Para Birimi
		private $DocumentCurrency 							= "TurkishLira";
		
		//Arşivlenen Fatura
		private $Archived 									= true;
		
		//!!!!
		private $IsPerson 									= false;
		
		//Faturanın asıl/suret bilgisi
		private $CopyIndicator 								= false;
		
		//Fatura Tipi
		private $PartyType 									= "Ozel";
		
		//Kdv Tipi
		private $TaxType 									= "KDVGercek";
		
		//Fatura İtem Tipi
		private $QuantityType 								= "Adet";
		
		//İskonto/Artırım Oranı 
		private $AllowanceChagreMultiplierFactor 			= 0;
		
		//İskonto/Artırım Tutarı 
		private $AllowanceChargeAmount 						= 0;
		
		//Satılan ürünler
		private $Lines 										= array();
		
		//Fatura Bilgileri
		private $invoice 									= array();
		
		//Son Gönderilen Fatura Bilgileri
		private $invoiceSendArr 							= array();
		
		//Fatura Tarihi
		private $OrderDate 									= null;
		
		//Fatura Tarihi
		private $IssueDate 									= null;
		
		//Zarf Tarihi
		private $EnvelopeDate 								= null;
		
		//Faturanın Sistem Durumu
		private $Status 									= 1;
		
		//Fatura Toplam Vergi Toplamı
		private $TotalTaxAmount 							= 0;
		
		//Dönem Süresi
		private $PeriodDuration 							= 1;
		
		//Fatura Ceza Tutarı
		private $PenaltyAmount 								= 0;
		
		//İskonto/Artırım Tutarı
		private $AllowanceChargeMultiplierFactor 			= 0;
		
		//İskonto/Artırım Matrahı
		private $AllowanceChargeBaseAmount 					= 0;
		
		//Toplam İskonto Tutarı
		private $AllowanceTotalAmount 						= 0;
		
		//İskonto/Artırım Tutarı
		private $TotalAllowanceChargeAmount 				= 0;
		
		//Vergi Hariç satır toplamı 
		private $TotalLineExtensionAmount 					= 0;
		
		//Vergiler hariç toplam tutar  
		private $TotalTaxExlusiveAmount 					= 0;
		
		//Vergiler dahil toplam tutar  
		private $TotalTaxInclusiveAmount 					= 0;
		
		//Ücret Toplam Tutarı
		private $ChargeTotalAmount 							= 0;
		
		//Ödenecek Toplam Yuvarlama Tutarı
		private $TotalPayableRoundingAmount 				= 0;
		
		//Ödenecek Toplam Tutar
		private $TotalPayableAmount 						= 0;
		
		//İskonto ise “N”, artırım ise “Y”
		private $AllowanceChargeIndicator 					= "N";
		
		//E-Arşiv
		private $DeliveryType 								= "A";
		
		//Fatura Gönderim Metodu
		private $SendMethod 								= "ELEKTRONIK";

		//
		private $PostboxAlias 								= "defaultpk";

		//
		private $XSLTName 								    = "derskosem.xslt";
		
		//Hata Listesi
		private $ErrorList 									= array();
		
		
		public function __construct($par = array())
		{
			try{	
				$this->	OrderDate		= $this->OrderDate();	
				$this->	IssueDate		= $this->OrderDate();	
				$this->	EnvelopeDate	= $this->OrderDate();	
				if(count($par) > 0)
					foreach($par as $key=>$val)
						$this->$key = $val;
				
				if (class_exists("SOAPClient")) {
					parent::__construct($this->WSDLUrl);
					
				}else
				{
					throw new Exception("Error: SOAPClient Could not load.");
				}
			}catch(Exception $e)
			{
				$this->ErrorList[] =  'Message: ' .$e->getMessage();
			}
		}
		
		public function OrderDate()
		{
			return date('Y-m-d')."T".date("H:i:s");
		}
		
		public function tokenRegister()
		{
			try{
				if($this->token == null) 
				{
					$token = $this->OturumAc(array(
						'kullaniciAdi' 		=> $this->kullaniciAdi,
						'kullaniciSifresi' 	=> $this->kullaniciSifresi
					));
					$this->token = $token->OturumAcResult;
				}
				return $this->token;
			}catch(Exception $e)
			{
				$this->ErrorList[] = 'Message: ' .$e->getMessage();
			}
			return false;
		}
		
		public function billHolder()
		{
			if(count($this->member) > 0)
			{
				$this->member["PartyType"] 		= $this->PartyType;
				$this->member["IsPerson"] 		= $this->IsPerson;
				return true;
			}
			return false;
		}
		
		public function invoiceLines()
		{
			if(count($this->Lines) > 0)
			{
				$LineOrderNo = 1;
				foreach($this->Lines as $key => $val)
				{
					$this->Lines[$key]["LineOrderNo"] 						= $LineOrderNo;
					$this->Lines[$key]["AllowanceChagreMultiplierFactor"] 	= $this->AllowanceChagreMultiplierFactor;
					$this->Lines[$key]["AllowanceChargeAmount"] 			= $this->AllowanceChargeAmount;
					$this->Lines[$key]["ItemCurrency"] 						= $this->ItemCurrency;
					$this->Lines[$key]["QuantityType"] 						= $this->QuantityType;
					$this->Lines[$key]["TaxSubtotals"] 						= array(
																						[
																							"TaxableAmount"			=> number_format(($val["ItemPrice"]/1.18),2),
																							"TaxAmount" 			=> number_format(($val["ItemPrice"]/1.18*0.18),2),
																							"TaxPercent" 			=> $this->kdv,
																							"TaxType" 				=> $this->TaxType,
																							"CalculationSequenceNo" => $LineOrderNo,
																						],
																					);
					$this->TotalLineExtensionAmount 						+= number_format(($val["ItemPrice"]/1.18),2) * $val["InvoicedQuantity"];
                    $this->TotalTaxExlusiveAmount 							+= number_format(($val["ItemPrice"]/1.18),2);
                    $this->TotalTaxAmount 									+= number_format(($val["ItemPrice"]/1.18*0.18),2);
                    $this->TotalTaxInclusiveAmount 							+= $val["ItemPrice"] * $val["InvoicedQuantity"];
					$this->ChargeTotalAmount 								+= $val["ItemPrice"] * $val["InvoicedQuantity"];
					$this->TotalPayableRoundingAmount 						+= $val["ItemPrice"] * $val["InvoicedQuantity"];
					$this->TotalPayableAmount 								+= $val["ItemPrice"] * $val["InvoicedQuantity"];
					$this->Lines[$key]["ItemPrice"]							= number_format(($val["ItemPrice"]/1.18),2);
					$LineOrderNo++;

				}
				return true;
			}
			return false;
		}
		
		public function invoiceInfo()
		{
			if($this->billHolder() and $this->InvoiceReference != null and $this->invoiceLines())
			{
				$Rand 												= date("Ymd").mt_rand(1000,9999);
				$this->invoice["InvoiceId"] 						= $Rand;
				$this->invoice["CopyIndicator"] 					= $this->CopyIndicator;
				$this->invoice["CustomerParty"] 					= $this->member;
				$this->invoice["OrderDate"] 						= $this->OrderDate;	
				$this->invoice["OrderId"] 							= $Rand;
				$this->invoice["DocumentCurrency"] 					= $this->DocumentCurrency;
				$this->invoice["InvoiceReference"] 					= $this->InvoiceReference;
				$this->invoice["InvoiceType"] 						= $this->InvoiceType;
				$this->invoice["IssueDate"] 						= $this->IssueDate;
				$this->invoice["Profile"] 							= $this->Profile;
				$this->invoice["Lines"] 							= $this->Lines;
				$this->invoice["PeriodDuration"] 					= $this->PeriodDuration;
				$this->invoice["PenaltyAmount"] 					= $this->PenaltyAmount;
				$this->invoice["AllowanceChargeIndicator"] 			= $this->AllowanceChargeIndicator;
				$this->invoice["AllowanceChargeMultiplierFactor"] 	= $this->AllowanceChargeMultiplierFactor;
				$this->invoice["AllowanceChargeBaseAmount"] 		= $this->AllowanceChargeBaseAmount;
				$this->invoice["TotalAllowanceChargeAmount"] 		= $this->TotalAllowanceChargeAmount;
				$this->invoice["TotalLineExtensionAmount"] 			= $this->TotalLineExtensionAmount;
				$this->invoice["TotalTaxExlusiveAmount"] 			= $this->TotalTaxExlusiveAmount;
				$this->invoice["TotalTaxInclusiveAmount"] 			= $this->TotalTaxInclusiveAmount;
				$this->invoice["TotalTaxAmount"] 					= $this->TotalTaxAmount;
				$this->invoice["AllowanceTotalAmount"] 				= $this->AllowanceTotalAmount;
				$this->invoice["ChargeTotalAmount"] 				= $this->ChargeTotalAmount;
				$this->invoice["TotalPayableRoundingAmount"] 		= $this->TotalPayableRoundingAmount;
				$this->invoice["TotalPayableAmount"] 				= $this->TotalPayableAmount;
				$this->invoice["Archived"] 							= $this->Archived;
				$this->invoice["Status"] 							= $this->Status;
				$this->invoice["DeliveryType"] 						= $this->DeliveryType;
				$this->invoice["SendMethod"] 						= $this->SendMethod;
				$this->invoice["EnvelopeDate"] 						= $this->EnvelopeDate;
				$this->invoice["InvoiceCount"] 						= 0;
				$this->invoice["FreeExport"] 						= false;
				$this->invoice["NotificationSend"] 					= $this->NotificationSend;
				$this->invoice["PostboxAlias"] 					    = $this->PostboxAlias;
				$this->invoice["XSLTName"] 					        = $this->XSLTName;
				return true;
			}
			return false;
		}
		
		public function invoiceSend()
		{
			if($this->invoiceInfo())
			{
				
				$this->invoiceSendArr = array(
									"token" 	=> $this->tokenRegister(),
									"invoice"	=> $this->invoice,
									"isDraft"	=> $this->isDraft,
								);		
				try{						
					return $this->FaturaGonder($this->invoiceSendArr);
				}catch(Exception $e)
				{
					$this->ErrorList[] = 'Message: ' .$e->getMessage();
				}
			}
			return false;
		}
		
		public function errorWrite()
		{
			if(count($this->ErrorList) > 0)
			{
				$err = '<div class="alert alert-danger">
							<i class="fa fa-warning"></i> 
							Aşağadaki nedenlerden Dolayı Fatura Oluşmadı.
							<ul>';
							
				foreach($this->ErrorList as $key => $val)
				{
					$err .= '<li>'.$val.'</li>';
				}
				
				$err .= '	</ul>
						</div>';
				return $err;
			}
		}
		
		public function getInvoice()
		{
			return $this->invoiceSendArr;
		}	
	
		public function sendInvoicePdf($invoiceNo, $downloadPdf = false)
		{
			if(is_numeric($invoiceNo))
			{
				$result=$this->GidenFaturaPdfAl(array("token" => $this->tokenRegister(), "faturaNo" => $invoiceNo));
				if($downloadPdf)
						return $this->downloadPdf($result->GidenFaturaPdfAlResult, $invoiceNo);
				else
				{
					header('Content-Type: application/pdf');
					echo $result->GidenFaturaPdfAlResult;
					return true;
				}
			}
			return false;
		}
		
		public function sendInvoicehtml($invoiceNo)
		{
			if(is_numeric($invoiceNo))
			{
				$result=$this->GidenFaturaHtmlAl(array("token" => $this->tokenRegister(), "faturaNo" => $invoiceNo));
				echo $result->GidenFaturaHtmlAlResult;
			}
			return false;
		}
		
		public function downloadPdf($base64Binary = null, $invoiceNo = null)
		{
			if($base64Binary != null and  $invoiceNo != null)
			{
				header('Content-Type: application/pdf');
				header('Content-Disposition: attachment; filename="DERSKOSEMEFATURA-'.$invoiceNo."-".date("Y-m-d-H-i-s").'.pdf"'); 
				echo $base64Binary;
			}
			return false;
		}
	}



