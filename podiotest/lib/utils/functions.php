<?php
/**
 * Simulate POST Request data if no POST Request received
 * @param array $data
 * @return array
 */
function getPostData ($data) 
{
    if(empty($data))
    {
        $data = array();
        $request = PODIO_POST_REQUEST;
        $datas = explode("&", $request);
        foreach($datas as $rawdata) {
            $aData = explode("=", $rawdata);
            $data[$aData[0]] = $aData[1];
        }
    }
    return $data;
}

/**
 * Build podio response array based on podioResponse.txt
 * @param PodioItem $item
 * @return array
 */
function buildPodioResponse (PodioItem &$item)
{
    $return = array();
    $return["item_id"] = $item->item_id;
    $return["title"] = $item->title;
    $return["created_on"] = $item->created_on->format('Y-m-d H:i:s');
    $return["app_item_id"] = $item->app_item_id;
    $aFields = array();
    
    //invoice with tax;
    $invoiceWTax = $item->fields["fakturabelop-inkl-mva"];
    $aFields["invoice-w-tax"] = array_merge(array("label" => $invoiceWTax->label), $invoiceWTax->values);
    
    //due date
    $dueDate = $item->fields["forfallsdato"];
    $aFields["duedate"] = array_merge(
                                        array("label" => $dueDate->label),
                                        array("start_date" => $dueDate->start_date->format('Y-m-d'), "end_date" => null)
                                     );
    
    //invoice number;    
    $invoiceNumber = $item->fields["fakturanummer"];
    $aFields["invoicenumber"] = $invoiceNumber->values;
    
    //invoice date;
    $invoiceDate = $item->fields["fakturadato"];
    $aFields["invoicedate"] = array_merge(
                                        array("label" => $invoiceDate->label),
                                        array("start_date" => $invoiceDate->start_date->format('Y-m-d'), "end_date" => null)
                                     );
    
    //invoice amount;
    $invoiceAmount = $item->fields["fakturabelop"];
    $aFields["invoiceamount"] = array_merge(array("label" => $invoiceAmount->label), $invoiceAmount->values);
    
    //shipping;
    $shipping = $item->fields["frakt"];
    $aFields["shipping"] = array_merge(array("label" => $shipping->label), $shipping->values);
    
    //tax;
    $tax = $item->fields["mva"];
    $aFields["tax"] = array_merge(array("label" => $tax->label), $tax->values);
    
    //customer
    $customer = $item->fields["kunde"];
    $aFields["customer"] = array_merge(array("label" => $customer->label), array($customer->values[0]->item_id));
    
    //order   
    $order = $item->fields["ordre"];
    $aFields["order"] = array_merge(array("label" => $order->label), array($order->values[0]->item_id)); 
    
     //status   
    $status = $item->fields["status"];
    $aFields["status"] = array_merge(array("label" => $status->label), array($status->values[0]["id"]));
    
    $return["fields"] = $aFields;
    return $return;
}

/**
 * Generates an HTML string based on a template file
 * @param array $data
 * @return string
 */
function generateInvoiceHtml(array &$data)
{
    $html = '';
    $view = new View();
    $view->setTemplateFile(TEMPLATEFILE);
    $view->data = $data;
    $html = $view->render();
    return $html;
}

/**
 * Converts the HTML string into PDF
 * @param String $html
 * @return string: the full path of pdf file
 * @throws HTML2PDF_exception
 */
function generateInvoicePDF( $html)
{
    $outputfile = null;
    try
    {
        
        $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
        $html2pdf->writeHTML($html, FALSE);
        //$html2pdf->createIndex('Sommaire', 25, 12, false, true, 1);
        $outputfile = PDF_DIR.'Invoice-'.time().'.pdf';
        $html2pdf->Output($outputfile, 'F');
    }
    catch(HTML2PDF_exception $e) {
        throw $e;
    }
    
    return $outputfile;
}

/**
 * Reverse function of print_r : Build an array from a print_r output
 * @param string $in
 * @return array
 */
function print_r_reverse($in) { 
    $lines = explode("\n", trim($in)); 
    if (trim($lines[0]) != 'Array') { 
        // bottomed out to something that isn't an array 
        return $in; 
    } else { 
        // this is an array, lets parse it 
        if (preg_match("/(\s{5,})\(/", $lines[1], $match)) { 
            // this is a tested array/recursive call to this function 
            // take a set of spaces off the beginning 
            $spaces = $match[1]; 
            $spaces_length = strlen($spaces); 
            $lines_total = count($lines); 
            for ($i = 0; $i < $lines_total; $i++) { 
                if (substr($lines[$i], 0, $spaces_length) == $spaces) { 
                    $lines[$i] = substr($lines[$i], $spaces_length); 
                } 
            } 
        } 
        array_shift($lines); // Array 
        array_shift($lines); // ( 
        array_pop($lines); // ) 
        $in = implode("\n", $lines); 
        // make sure we only match stuff with 4 preceding spaces (stuff for this array and not a nested one) 
        preg_match_all("/^\s{4}\[(.+?)\] \=\> /m", $in, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER); 
        $pos = array(); 
        $previous_key = ''; 
        $in_length = strlen($in); 
        // store the following in $pos: 
        // array with key = key of the parsed array's item 
        // value = array(start position in $in, $end position in $in) 
        foreach ($matches as $match) { 
            $key = $match[1][0]; 
            $start = $match[0][1] + strlen($match[0][0]); 
            $pos[$key] = array($start, $in_length); 
            if ($previous_key != '') $pos[$previous_key][1] = $match[0][1] - 1; 
            $previous_key = $key; 
        } 
        $ret = array(); 
        foreach ($pos as $key => $where) { 
            // recursively see if the parsed out value is an array too 
            $ret[$key] = print_r_reverse(substr($in, $where[0], $where[1] - $where[0])); 
        } 
        return $ret; 
    } 
} 

/**
 * Attach a file to an item
 * @param PodioItem $item
 * @param string $filepath
 */
function podioAttachFile(PodioItem &$item, $filepath)
{ 
    $fullpaths = explode("/", $filepath);
    $filename = $fullpaths[count($fullpaths) - 1];  
    $upload = PodioFile::upload($filepath, $filename);
    PodioFile::attach($upload->file_id, array('ref_type' => 'item', 'ref_id' => $item->item_id));
}



