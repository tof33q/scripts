<?php
class Logger {
    
    public static $dir;
    public static $logfile ;
    public static $rustart ;
    public static $ru ;
    public static $startmicrotime;
    public static $endmicrotime;
    public static $startime;
    public static $endtime;
    
    public static $request;
    public static $action;
    public static $action_type;
    public static $action_params;
    

    public static $rusage_start;
    public static $rusage_end;
    
    public static $execution_time;
    public static $system_calls_time;
    
    public static $script;
    public static $mode  = 'FILE';
    
    public static $logId;
    public static $logdata;
    
    public static $memory;
    public static $cpu_start;
    public static $cpu_end;
    
    public static $swap;
    public static $page_faults;        
    
    public static $db = null;
    
    public static function open_db()
    {
         self::$db = new PDO('mysql:host=localhost;dbname=yourdb;charset=utf8mb4', 'username', 'passwrod');
    }
    
    public static function open($dir)
    {
        
        if ($dir == 'DB') {
            self::$mode = 'DB';
            
            
            self::open_db();
            
            self::$request = $_SERVER['REQUEST_URI'];
    
            self::$startime         = time();
            self::$startmicrotime   = microtime(true);
            self::$rusage_start = getrusage();
            self::$cpu_start = file('/proc/stat'); 
             
            //adding new log
            $query = "INSERT INTO logs (url, script, ip, referer, start_time, micro_start_time, rusage_start) VALUES ('" . self::$request . "', '" . self::$script . "', '" . self::ip() . "', '" . self::referer() . "', '" . self::$startime . "', '" . self::$startmicrotime . "', '" . serialize(self::$rusage_start) . "')";
            
            //echo $query;
            
            $result = self::$db->exec($query);
            
            
            self::$logId = self::$db->lastInsertId();
            
            
        } else {
            
            list($y, $m, $d) = explode("-", date("Y-m-d"));
            list($h, $i, $s) = explode(":", date("H:i:s"));
            
            self::$dir = $dir . "/$y/$m/$d/$h";
            
             
            
            if (!is_dir(self::$dir)) {
                mkdir(self::$dir, 0755, true);
            }
            
            self::$logfile = fopen(self::$dir . "/" . $i . ".log", 'a');
            
            self::log("\n================");
            self::log("\n New Log Start");
            self::log("\n================\n");
        }
        
    }
    
    public static function close()
    {
        if (self::$mode == 'DB') {
            
            self::$endmicrotime = microtime(true);
            self::$endtime = time();
          
            self::$rusage_end = getrusage();
            self::$execution_time = Logger::rutime("utime");
            self::$system_calls_time = Logger::rutime("stime");
            self::$cpu_end = file('/proc/stat');         
            
            self::$memory = memory_get_usage();
             
            self::$swap = self::$rusage_end['ru_nswap'];
            self::$page_faults = self::$rusage_end['ru_majflt'];        
    
    
            $info1 = explode(" ", preg_replace("!cpu +!", "", self::$cpu_start[0])); 
            $info2 = explode(" ", preg_replace("!cpu +!", "", self::$cpu_end[0])); 
            $dif = array(); 
            $dif['user'] = $info2[0] - $info1[0]; 
            $dif['nice'] = $info2[1] - $info1[1]; 
            $dif['sys'] = $info2[2] - $info1[2]; 
            $dif['idle'] = $info2[3] - $info1[3]; 
            $total = array_sum($dif); 
            $cpu = array(); 
            foreach($dif as $x => $y) {
                $cpu[$x] = round($y / $total * 100, 1);
            }
            
           $load = sys_getloadavg();
            
            $result = self::$db->exec("UPDATE logs 
                SET
                end_time = '" . self::$endtime . "',
                micro_end_time = '" . self::$endmicrotime . "',
                rsuage_end = '" . serialize(self::$rusage_end) . "',
                action = '" . self::$action . "',
                action_type = '" . self::$action_type . "',
                action_params = '" . serialize(self::$action_params) . "',
                exec_time = '" . self::$execution_time . "',
                system_calls_time = '" . self::$system_calls_time . "',
                log_data = '" . self::$logdata  . "',
                
                cpu = '" . serialize($cpu)  . "',
                memory = '" . self::$memory  . "',
                swap = '" . self::$swap  . "',
                page_faults = '" . self::$page_faults  . "',
                load1 = '" . $load[0]  . "',
                load5 = '" . $load[1]  . "',
                load15 = '" . $load[2]  . "'
                                                                                                                
             
                   WHERE id = " . self::$logId 
                );
            
            self::save_db();
        } else {
            fclose(self::$logfile);
        }
        
    }
    
    public static function save_db()
    {
        
    }
    
    public static function log($str)
    {
        if (self::$mode == 'DB') {
            self::$logdata .= $str . "\n";            
        } else {
            fwrite(self::$logfile, $str . "\n");                   
        }

    }
    
    
    public static function referer()
    {
        return $_SERVER["HTTP_REFERER"];
    }
    
    public static function rutime($index) {
        $rus = self::$rusage_start; 
        $ru = self::$rusage_end;
        
        
        return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
         -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
    }
    
    public static function ip()
    {
 
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
     
    }
    
    public static function install_db()
    {
        $sql = "CREATE TABLE `logs` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `memory` float DEFAULT NULL,
         `exec_time` int(11) DEFAULT NULL,
         `start_time` int(11) DEFAULT NULL,
         `end_time` int(11) DEFAULT NULL,
         `micro_start_time` int(11) DEFAULT NULL,
         `micro_end_time` int(11) DEFAULT NULL,
         `system_calls_time` int(11) DEFAULT NULL,
         `page_faults` int(11) DEFAULT NULL,
         `swap` float DEFAULT NULL,
         `load1` float DEFAULT NULL,
         `load5` float DEFAULT NULL,
         `action` varchar(255) DEFAULT NULL,
         `url` varchar(255) NOT NULL,
         `script` varchar(255) NOT NULL,
         `ip` varchar(28) NOT NULL,
         `referer` varchar(255) DEFAULT NULL,
         `rusage_start` text,
         `rsuage_end` text,
         `action_type` varchar(50) DEFAULT NULL,
         `action_params` text,
         `log_data` text,
         `load15` float DEFAULT NULL,
         `cpu` float NOT NULL,
         PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1
        ";
        
        self::open_db();
        $result = self::$db->exec($query);
        print_r($result);
    }
}
