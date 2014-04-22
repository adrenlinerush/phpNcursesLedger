<?php
$n = ncurses_init();
$currently_selected=1;
$entries = renderScreen($currently_selected);
while(1) {
  $input = ncurses_getch();

  if(($input == 27)||($input == 113)) {
    //Escape or "q" key (Quit)
    ncurses_end();
    exit;
  }elseif($input == NCURSES_KEY_UP){
    $currently_selected--;
    if($currently_selected < 1){ $currently_selected = 1; }
    $entries = renderScreen($currently_selected);
  }elseif( $input == NCURSES_KEY_DOWN){
    $currently_selected++;
    if($currently_selected >= $entries){ $currently_selected = $entries; }
    $entries = renderScreen($currently_selected);
  }elseif( $input == NCURSES_KEY_PPAGE ) {
    //Page Up
    $currently_selected -=15;
    if($currently_selected < 1){ $currently_selected = 1; }
    $entries = renderScreen($currently_selected);
  }elseif( $input == NCURSES_KEY_NPAGE ) {
    //Page Down 
    $currently_selected +=15;
    if($currently_selected >= $entries){ $currently_selected = $entries; }
    $entries = renderScreen($currently_selected);
  }elseif($input == "100") {
    //"d" key (Delete)
    deleteEntry($currently_selected);
    $entries = renderScreen($currently_selected);
  }elseif($input == "97") {
    //"a" key (Add Entry)
    $entries = addEntry();
    renderScreen($entries);
  }elseif($input == "99") {
    //"c" key (Cleared)
    toggleCleared($currently_selected);
    renderScreen($entries);
  }
}

function toggleCleared($currently_selected){
  $handle = fopen("ledger.csv", "r");
  if ($handle) {
    $n=0;
    while (($line = fgets($handle)) !== false) {
     if ($n==$currently_selected) {
        $clrline = $line;
     }
     $n++;
    }
  }
  $aryline=explode(',',$clrline);
  if ($aryline[3]=="x"){
   $aryline[3]="";
  } else {
   $aryline[3]="x";
  }
  $newline=implode(",",$aryline);
  $ledger = file_get_contents("ledger.csv");
  $ledger = str_replace($clrline,$newline,$ledger);
  file_put_contents("ledger.csv",$ledger);
  $mw = ncurses_newwin(0,0,0,0);
  ncurses_getmaxyx($mw,$lines,$columns);
  $iw = ncurses_newwin(14,$columns,$lines-14,0);
  ncurses_wborder($iw, 0,0, 0,0, 0,0, 0,0);
  ncurses_wattron($iw,NCURSES_A_REVERSE);
  ncurses_mvwaddstr($iw, 0, 1, "Toggle Cleared");
  ncurses_wattroff($iw,NCURSES_A_REVERSE);

  ncurses_mvwaddstr($iw, 2, 1, "[".$aryline[3]."] Cleared");
  ncurses_mvwaddstr($iw, 3, 1, "Changed: $clrline");
  ncurses_mvwaddstr($iw, 4, 1, "To: $newline");
  ncurses_mvwaddstr($iw, 5, 1, "Press Any Key to Continue...");
  $input = ncurses_wgetch($iw);

}

function deleteEntry($currently_selected){
 $handle = fopen("ledger.csv", "r");
  if ($handle) {
    $n=0;
    while (($line = fgets($handle)) !== false) {
     if ($n==$currently_selected) {
        $delete = $line;
     }
     $n++;
    }
  }
  $mw = ncurses_newwin(0,0,0,0);
  ncurses_getmaxyx($mw,$lines,$columns);
  $iw = ncurses_newwin(14,$columns,$lines-14,0);
  ncurses_wborder($iw, 0,0, 0,0, 0,0, 0,0);
  ncurses_wattron($iw,NCURSES_A_REVERSE);
  ncurses_mvwaddstr($iw, 0, 1, "Delete Entry");
  ncurses_wattroff($iw,NCURSES_A_REVERSE); 

  ncurses_mvwaddstr($iw, 2, 1, "Are you sure you want to delete the Entry(y/n)?");
  ncurses_mvwaddstr($iw, 3, 1, $delete);
  $input = ncurses_wgetch($iw);
  if ($input == 121) {
    $ledger = file_get_contents("ledger.csv");
    $ledger = str_replace($delete,"",$ledger);
    file_put_contents("ledger.csv",$ledger);
 }
}

function addEntry(){
  $mw = ncurses_newwin(0,0,0,0);
  ncurses_getmaxyx($mw,$lines,$columns);
  $iw = ncurses_newwin(14,$columns,$lines-14,0);
  ncurses_wborder($iw, 0,0, 0,0, 0,0, 0,0);
  ncurses_wattron($iw,NCURSES_A_REVERSE);
  ncurses_mvwaddstr($iw, 0, 1, "Add Entry");
  ncurses_wattroff($iw,NCURSES_A_REVERSE);
 
  $input = "";
  ncurses_mvwaddstr($iw, 2, 1, "(D)eposit or (W)ithdrawl?");
  while(($input != 100) && ($input != 119)){
    $input = ncurses_wgetch($iw);
    if ($input == 100) {
      $type = "Deposit";
    }  elseif ($input == 119) {
      $type = "Withdrawl";
    }
  }   
  $input = "";
  $edate = "";
  ncurses_mvwaddstr($iw, 3, 1, "Date (" . date("m/d/Y") . "):");
  while($input != 13) {
    $input = ncurses_wgetch($iw);
    if ($input !=13) {
      $edate .= chr($input);
    }
  }
  if ($edate == "") { $edate = date("m/d/Y"); }
  $input = "";
  $desc = "";
  ncurses_mvwaddstr($iw, 4, 1, "Description? ");
  while($input != 13) {
    $input = ncurses_wgetch($iw);
    if ($input !=13) {
      $desc .= chr($input);
   }
  }
  $input = "";  
  $amount = "";
  ncurses_mvwaddstr($iw, 5, 1, "Amount: ");
  while($input != 13) {
    $input = ncurses_wgetch($iw);
    if ($input !=13) {
      $amount .= chr($input);
    }
  }
  $input = "";
  $chk = "";
  ncurses_mvwaddstr($iw, 6, 1, "Check #: ");
  while($input != 13) {
    $input = ncurses_wgetch($iw);
    if ($input !=13) {
      $chk .= chr($input);
    }
  }
  $input = "";
  $clrd = "";
  ncurses_mvwaddstr($iw, 7, 1, "Cleared(y/n)?");
  while (($input != 121) && ($input != 110)) {
    $input = ncurses_wgetch($iw);
    if ($input == 121) {
      $clrd = "x";
    }
  }
  $input = "";
  $entry = $edate . "," . $chk . "," . $desc . "," . $clrd . ",";  
  if ($type == "Deposit") {
    $entry .= $amount . ",";
  } else {
    $entry .= "," . $amount;
  }
  ncurses_mvwaddstr($iw, 8, 1, "Is entry correct(y/n)?");
  ncurses_mvwaddstr($iw, 9, 1, $entry);
  while (($input != 121) && ($input != 110)) {
    $input = ncurses_wgetch($iw);
    if ($input == 121) {
      file_put_contents("ledger.csv",$entry."\n",FILE_APPEND); 
    }
  }
  $linecount = 0;
  $handle = fopen("ledger.csv", "r");
  while(!feof($handle)){
    $line = fgets($handle);
    $linecount++;
  }
  return $linecount-1;
}

function renderScreen($currently_selected){
  $mw = ncurses_newwin(0,0,0,0);
  ncurses_getmaxyx($mw,$lines,$columns);
  ncurses_border(0,0, 0,0, 0,0, 0,0);

  ncurses_attron(NCURSES_A_REVERSE);
  ncurses_mvaddstr(0,1,"Checkbook Ledger");
  ncurses_attroff(NCURSES_A_REVERSE);

  $dw = ncurses_newwin($lines-15,12,1,0);
  ncurses_wborder($dw, 0,0, 0,0, 0,0, 0,0);
  ncurses_mvwaddstr($dw, 0, 2, "Date");

  $nw = ncurses_newwin($lines-15,8,1,12);
  ncurses_wborder($nw, 0,0, 0,0, 0,0, 0,0);
  ncurses_mvwaddstr($nw, 0, 1, "ChkNum");

  $sw = ncurses_newwin($lines-15,$columns-50,1,20);
  ncurses_wborder($sw, 0,0, 0,0, 0,0, 0,0);
  ncurses_mvwaddstr($sw, 0, 2, "Description");

  $cw = ncurses_newwin($lines-15,5,1,$columns-50);
  ncurses_wborder($cw, 0,0, 0,0, 0,0, 0,0);
  ncurses_mvwaddstr($cw, 0, 1, "Clr");

  $aw = ncurses_newwin($lines-15,15,1,$columns-45); 
  ncurses_wborder($aw, 0,0, 0,0, 0,0, 0,0);
  ncurses_mvwaddstr($aw, 0, 2, "Deposit");

  $ww = ncurses_newwin($lines-15,15,1,$columns-30);
  ncurses_wborder($ww, 0,0, 0,0, 0,0, 0,0);
  ncurses_mvwaddstr($ww, 0, 1, "Withdrawl");

  $lw = ncurses_newwin($lines-15,15,1,$columns-15);
  ncurses_wborder($lw, 0,0, 0,0, 0,0, 0,0);
  ncurses_mvwaddstr($lw, 0, 2, "Balance");

  $iw = ncurses_newwin(14,$columns,$lines-14,0);
  ncurses_wborder($iw, 0,0, 0,0, 0,0, 0,0);
  ncurses_wattron($iw,NCURSES_A_REVERSE);
  ncurses_mvwaddstr($iw, 0, 1, "Summary");
  ncurses_wattroff($iw,NCURSES_A_REVERSE);

  if($currently_selected == ""){$currently_selected = 1;}
  $handle = fopen("ledger.csv", "r");
  if ($handle) {
    $n=0;
    $bal=0;
    $qtypnd=0;
    $pnd=0;
    while (($line = fgets($handle)) !== false) {
      if ($n==0){
        $bal=$line;
        $clrd=$line;
      } else {
        $elements = explode(",",$line);
        $bal += $elements[4];
        $bal -= $elements[5];
        if ($elements[3] != "") {
          $clrd += $elements[4];
          $clrd -= $elements[5];
        } else {
          $pnd += $elements[4]; 
          $pnd -= $elements[5];
          $qtypnd ++;
        }
        if ((($n >= $currently_selected) && ($n >= ($currently_selected-($lines-15)))) || (($currently_selected <= ($lines-15)) && ($n <= ($lines-15)))) { 
          if ($n == $currently_selected){
            ncurses_wattron($dw,NCURSES_A_REVERSE);
            ncurses_wattron($nw,NCURSES_A_REVERSE); 
            ncurses_wattron($sw,NCURSES_A_REVERSE); 
            ncurses_wattron($cw,NCURSES_A_REVERSE); 
            ncurses_wattron($aw,NCURSES_A_REVERSE); 
            ncurses_wattron($ww,NCURSES_A_REVERSE); 
            ncurses_wattron($lw,NCURSES_A_REVERSE); 
          }
          ncurses_mvwaddstr($dw, $n, 1, $elements[0]);
          ncurses_mvwaddstr($nw, $n, 1, $elements[1]);
          ncurses_mvwaddstr($sw, $n, 1, $elements[2]);
          ncurses_mvwaddstr($cw, $n, 1, $elements[3]);
          ncurses_mvwaddstr($aw, $n, 1, $elements[4]);
          ncurses_mvwaddstr($ww, $n, 1, $elements[5]);
          ncurses_mvwaddstr($lw, $n, 1, $bal);
          if ($n == $currently_selected){
            ncurses_wattroff($dw,NCURSES_A_REVERSE);
            ncurses_wattroff($nw,NCURSES_A_REVERSE);
            ncurses_wattroff($sw,NCURSES_A_REVERSE); 
            ncurses_wattroff($cw,NCURSES_A_REVERSE);
            ncurses_wattroff($aw,NCURSES_A_REVERSE);
            ncurses_wattroff($ww,NCURSES_A_REVERSE);
            ncurses_wattroff($lw,NCURSES_A_REVERSE); 
          }
        }
      }
      $n++;
    }
  } else {
  }
  fclose($handle);

  ncurses_mvwaddstr($iw, 2, 1, "Cleared: $clrd");
  ncurses_mvwaddstr($iw, 3, 1, "Future: $bal");
  ncurses_mvwaddstr($iw, 4, 1, "Pending: $pnd");
  ncurses_mvwaddstr($iw, 5, 1, "Qty Pending: $qtypnd");

  ncurses_refresh();
  ncurses_wrefresh($lw);
  ncurses_wrefresh($nw);
  ncurses_wrefresh($sw);
  ncurses_wrefresh($cw);
  ncurses_wrefresh($aw);
  ncurses_wrefresh($ww);
  ncurses_wrefresh($dw);
  ncurses_wrefresh($iw);
  return $n-1;
}
?>
