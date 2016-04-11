<?php

$masterFileUrl = $argv[1];

$outputDir = __DIR__;
if(array_key_exists(2, $argv))
{
    $outputDir = $argv[2];
}

if( ! is_writable($outputDir))
{
    echo "\nOutput dir not writage\n";
    exit(2);
}


function canonicalize($address)
{
    $address = explode('/', $address);
    $keys = array_keys($address, '..');

    foreach ($keys as $keypos => $key)
    {
        array_splice($address, $key - ($keypos * 2 + 1), 2);
    }

    $address = implode('/', $address);
    $address = str_replace('./', '', $address);

    return $address;
}



$masterFileUrl = str_replace('base64_init=1', 'base64_init=0', $masterFileUrl);

$cnt = file_get_contents($masterFileUrl);
if(empty($cnt))
{
    echo "\nInvalid master file URL\n";
    exit(1);
}

$jsonInfos = json_decode($cnt, true);
if( ! is_array($jsonInfos))
{
    echo "\nInvalid master file\n";
    exit(1);
}

$baseUrl = canonicalize(dirname($masterFileUrl)."/".$jsonInfos["base_url"]);

$fluxCount = count($jsonInfos['video']);

for($fluxNumber=0;$fluxNumber < $fluxCount;$fluxNumber++)
{
    $bitrate = $jsonInfos['video'][$fluxNumber]['bitrate'];
    $outputFile = $outputDir."/output-".$fluxNumber."-".$bitrate.".mp4";
    
    file_put_contents($outputFile, "");    
    
    $rep = $jsonInfos['video'][$fluxNumber]["base_url"];
    $initSegment = $jsonInfos['video'][$fluxNumber]["init_segment"];
    echo "\n".$initSegment;
    
    
    $tsUrl = $baseUrl.$rep."".$initSegment;
    $cnt = file_get_contents($tsUrl);
    file_put_contents($outputFile, $cnt, FILE_APPEND);

    $nbSeg = count($jsonInfos['video'][$fluxNumber]['segments']);
    foreach($jsonInfos['video'][$fluxNumber]['segments'] as $i=>$seg)
    {
        //echo ".";
        echo "\n".(1+$fluxNumber).'/'.$fluxCount.' - '.(1+$i)."/".$nbSeg." - ".$seg['url'];
        $tsUrl = $baseUrl."/".$rep."".$seg['url'];
        $cnt = file_get_contents($tsUrl);
        file_put_contents($outputFile, $cnt, FILE_APPEND);
    }
}
echo "\nFIN\n";
