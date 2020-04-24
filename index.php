<?php 
  include("class.turkkep.php");
  $member  = array(
        "PersonName" 		=> "Lorem",
        "PersonSurname" 	=> "ipsum",
        "Country" 			=> "Türkiye",
        "City" 				=> "Eskişehir",
        "CitySubDivision" 	=> "Tepebaşı",
        "TaxOrIdNo" 		=> "11111111111", // TC/VKN
        "Email" 			=> "uye@eposta.com",
        "PhoneNumber" 		=> "05532209516"
    );

    $par = array(
        "kullaniciAdi" 			=> "", //Türkkep Kullanıc adı
        "kullaniciSifresi" 		=> "", //Türkkep Şifre
        "WSDLUrl" 				=> "https://efintws.turkkep.com.tr/EFaturaEntegrasyon.asmx?wsdl",						
        "InvoiceReference" 		=> "DRS".date("Y").str_pad($id+2000, 9, "0", STR_PAD_LEFT),
        "OrderDate" 		    => date("Y-m-d", "2020-04-23 22:22:22"."T".date("H:i:s", "2020-04-23 22:22:22"),
        "IssueDate" 		    => date("Y-m-d", "2020-04-23 22:22:22")."T".date("H:i:s", "2020-04-23 22:22:22"),
        "EnvelopeDate" 		    => date("Y-m-d", "2020-04-23 22:22:22")."T".date("H:i:s", "2020-04-23 22:22:22"),
        "Lines"					=> array(
            [
                "ItemName" 				=> "Hizmet Bedeli",
                "Note" 					=> "Hizmet Bedeli",
                "InvoicedQuantity" 		=> 1,
                "ItemPrice" 			=> number_format("120,30",2)
            ]
        ),
        "member"				=>	$member,
    );
    $a 			= new TurkKepEFatura($par);
    $faturaNo 	= $a->invoiceSend();

    if($faturaNo)
    {
      $FaturaResult = $faturaNo->FaturaGonderResult;
      echo '<div class="alert alert-success"><i class="fa fa-check"></i> Fatura işleminiz başarıyla kaydedildi.</div>';
    }
    else echo $a->errorWrite();
