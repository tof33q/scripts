<?php
$content = "";
$code = "";
function tab($tabs = 1) {
    $str = "";
    for ($i = 0; $i < ($tabs * 4); $i++) {
        $str .= "&nbsp;";   
    }
    return $str;
}

function ln($lines = 1) {
    $str = "";
    
    for ($i = 0; $i < $lines; $i ++) {
        $str .= "<br />";        
    }

    return $str;
}

if (isset($_POST['vars'])) {
    $content = $_POST['vars'];    
    $list = preg_split("/\\r\\n|\\r|\\n/", $content);
    
    
    foreach ($list as $var) {
        $code .= tab() . 'protected $_'. $var . ';' . ln();
    }
    
    $code .= ln();
    
    foreach ($list as $var) {
        $code .= tab() . '/* set ' . ucfirst($var) . ' */' . ln();
        $code .= tab() . 'public function set' . ucfirst($var) . '($value) {';
        $code .= ln() . tab(2) . '$this->_' . $var . ' = $value;';
        $code .= ln() . tab(2) . 'return $this;';
        $code .= ln() . tab() . '}' . ln(2);
        $code .= tab() . '/* get ' . ucfirst($var) . ' */' . ln();
        $code .= tab() . 'public function get' . ucfirst($var) . '() {';
        $code .= ln() . tab(2) . 'return $this->_' . $var . ';';
        $code .= ln() . tab() . '}' . ln(2);
    }
}

?>
<form method="post">
    <div>
        <textarea name="vars" cols=120 rows=12><?php echo $content?></textarea>
    </div>
    
    <input type="submit" value="generate">
</form>
<div>
    <iframe id="idContent" width="600" height="280"></iframe>
</div>
<script>
window.onload = function() {
    var iframes = document.getElementsByTagName('iframe');
    for (var i = 0, len = iframes.length, doc; i < len; ++i) {
        doc = iframes[i].contentDocument || iframes[i].contentWindow.document;
        //doc.designMode = "on";
        doc.write('<?php echo $code; ?>');
        doc.close();
    }
};
</script>
