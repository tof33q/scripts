<?php 
$page = empty($_GET['page']) ? 1 : $_GET['page']; 
$where_get = empty($_GET['where']) ? 'WHERE exec_time > 500 ORDER BY exec_time DESC' : $_GET['where'];
$date_start = empty($_GET['start']) ? date("Y-m-d H:i:s") : $_GET['start'];
$date_end = empty($_GET['end']) ? date("Y-m-d H:i:s") : $_GET['end'];
?>

<style>
    .collapse {
        max-height:100px;
        overflow-y:scroll;
    }
    .full {
        clear:both;
        width:100%;
    }
    .half {
        width:48%;
        float:left;
    }
    .red {
        font-weight:bold;
        color:#f00;
    }
    
    textarea {
        margin: 0px; 
        width: 570px; 
        height: 51px;    
    }
</style>
<div class="full">
    <div class="half">
        <form method="get" action="">
            <p>Start Date/Time <input type="text" name="start" value="<?php echo $date_start; ?>"></p>
            <p>End Date/Time <input type="text" name="end" value="<?php echo $date_end; ?>"></p>
            <p><input type="submit" value="search" name="search_dates"></p>
             
        </form>
    </div>
    <div class="half">
        <form method="get" action="">
            <p>Query <textarea name="where"><?php echo $where_get; ?></textarea></p>
             
            <p><input type="submit" value="search" name="search_where"></p>
        </form>
    </div>
</div>
<div class="full">
<?php
date_default_timezone_set('utc');

$db = new PDO('mysql:host=localhost;dbname=yourdb;charset=utf8mb4', 'username', 'passwrod');

$per_page = 100;

$offset = ($page - 1) * $per_page;


$order = " order by id DESC ";

if (!empty($_GET['search_where'])) {
    if (preg_match('#delete|update#i', $where_get)) {
        die("na na!");
    }
    
    $order = "";
    $where =  $where_get;
} else {
    $where = array();

    if (!empty($_GET['start'])) {
        $where[] = "start_time >= " . strtotime($_GET['start']);
    }

    if (!empty($_GET['end'])) {
        $where[] = "end_time <= " . strtotime($_GET['end']);
    }

    if (!empty($where)) {
        $where = "where " . implode(" and ", $where);
        
    } else {
        $where = "";
    }
}

$query = "SELECT SQL_CALC_FOUND_ROWS * FROM logs $where $order limit $offset, $per_page";

echo $query . '</div>';

$stmt = $db->prepare($query);

//$stmt->bindValue(':limit', 100, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_rows = $db->query('SELECT FOUND_ROWS();')->fetch(PDO::FETCH_COLUMN);

$total_pages = ceil($total_rows / $per_page);

if (!$rows) {
    die( "no data" );
}

$head = current($rows);

echo '<table width=100% cellpadding=2 cellspacing=2 border=1><tbody><thead>';
foreach ($head as $title => $val) {
    echo '<td>' . $title . '</td>';
}
echo '</thead><tbody>';

foreach ($rows as $row) {
    echo '<tr>';
    foreach ($row as $col => $coldata) {
        
        if (function_exists('_filter_' . $col)) {
            $func = '_filter_' . $col;
            $coldata = $func($col, $row);    
        }
        
        
        if (strpos($coldata, "{") === false) {
            echo '<td><div class="collapse">' . nl2br($coldata) . '</div></td>';
        } else {
            $data = unserialize($coldata);
            echo '<td><div class="collapse">' . nl2br(var_export($data, 1)) . '</div></td>';
        }
        
    }
    echo '</tr>';
}

echo '</tbody></table>';

function _filter_start_time($col, $row) {
    $coldata = $row[$col];    
    return date("Y-m-d H:i:s", $coldata);
}
 
function _filter_micro_end_time($col, $row) {
    $end_time = $coldata = $row[$col];    
    if (!$coldata ) return '<span class="red">!</span>'; 
    $start_time = $row['micro_start_time'];    
    return '<span class="red">' . number_format($end_time - $start_time, 3) . " sec</span> \n " . $coldata;
}

function _filter_end_time($col, $row) {
    $end_time = $coldata = $row[$col]; 
    if (!$coldata ) return '<span class="red">!</span>';   
    $start_time = $row['start_time'];  
    $time = ($end_time - $start_time);
    if ($time % 60) {
        $time = floor($time/60) . ':' . ($time % 60) . ' mins';
    } else {
        $time = $time . " sec";
    }
    return '<span class="red">' . $time . '</span>' . " \n " 
    . date("Y-m-d H:i:s", $coldata);
}

function _filter_memory($col, $row) {
    $coldata = $row[$col];    
    if (!$coldata ) return '<span class="red">!</span>';   
    return '<span class="red">' . convert_memory($coldata) . "</span>\n" . $coldata;
}

function convert_memory($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}



function get_url($params)
{
    //$url = $_SERVER['REQUEST_URI'];
    $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    
    $qs = $_SERVER['QUERY_STRING'] ;
    parse_str($qs, $qsparams);
    
    unset($qsparams['page']);
    $qs = http_build_query(array_merge($params, $qsparams));
    return $url . '?' . $qs;
}

echo "<a href='" . get_url(array('page' => 1)) . "'>".'|<'."</a> "; // Goto 1st page  

for ($i=1; $i<=$total_pages; $i++) { 
    echo "<a href='" . get_url(array('page' => $i)) . "'>" . $i . "</a> "; 
}; 
echo "<a href='" . get_url(array('page' => $total_pages)) . "'>".'>|'."</a> "; // Goto last page
