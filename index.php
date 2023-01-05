<html>
<a href='index.php?run=true'>Run</a><br>
<script src="./script.js"></script>
</html>
<?php
function createDataSet() {
$filename = './patrimoine43-abandoned-urbex-locations.html';
$data = [];

$ignores = ['sale', 'vacant'];
$replaces = [
    "&#039;" => "'",
    "&quot;" => '"',
    '&amp;' => ','
];
$icons = [
    '#icon-1598-7CB342-nodesc' => ['château', 'chateau', 'pałac', 'schloss', 'castle'],
    '#icon-1602-795548-nodesc' => ['hotel', 'hôtel', 'hostel', 'resort'],
    '#icon-1635-880E4F-nodesc' => ['cinéma', 'cinema'],
    '#icon-1716-1A237E-nodesc' => ['train', 'gare'],
    '#icon-1807-E65100-nodesc' => ['hospital', 'hôpital', 'hopital'],
    '#icon-1603-1A237E-nodesc' => ['manoir', 'maison', 'casa', 'villa', 'house', 'haus'],
    '#icon-1565-FFD600-nodesc' => ['industrial', 'usine', 'factory'],
    '#icon-1546-880E4F-nodesc' => ['building', 'construction'],
    '#icon-1577-757575-nodesc' => ['restaurant'],
];
$defaultIcon = '#icon-1523-757575-nodesc';

function findIcon($str, $icons, $defaultIcon) {
    foreach($icons as $tag => $words) {
        foreach($words as $word) {
            if(strpos(strtolower($str), $word)) return $tag;
        }
    }
    return $defaultIcon;
}

function patchLine($line, $replaces) {
    foreach($replaces as $k => $v) {
        $line = str_replace($k, $v, $line);
    }
    return $line;
}

function convertCoord($str) {
    preg_match('#([0-9]+)°([0-9]+)\'([0-9]+.[0-9])"([A-Z])#', $str, $matches);
    if(count($matches) === 5) {
        $pos = in_array($matches[4], ['N', 'E']) ? 1 : -1;
        return $pos*($matches[1]+$matches[2]/60+$matches[3]/3600);
    }
    return $str;
}

function findInString($str, $arr) {
    foreach($arr as $item) {
        if(strpos($str, $item)) return false;
    }
    return true;
}

if ($file = fopen($filename, 'r')) {
    while(!feof($file)) {
        $line = patchLine(fgets($file), $replaces);
        preg_match('#.*HREF="(.*)" GUID.*IMAGE="(.*)" COLOR.*PRIVATE="0">(.*".{1}) (.*".{1}) (.*)<#', $line, $matches);
        
        if(count($matches) === 6 && findInString($matches[5], $ignores)) {
            $data[] = [
                'url' => $matches[1],
                'long' => convertCoord($matches[3]),
                'lat' => convertCoord($matches[4]),
                'name' => str_replace(',', '.', $matches[5]),
                'image' => $matches[2],
                'icon' => findIcon($matches[5], $icons, $defaultIcon)
            ];
        }
    }
    fclose($file);
}


$rowcount = 0;
$filecount = 0;
$type = 'kml';
$newline = true;

if($type === 'csv') {

    foreach($data as $row) {
        $rowcount++;
        if($newline === true) {
            $newline = false;
            $filecount++;
            $fp = fopen('export'.$filecount.'.csv', 'w');
            fwrite($fp, 'Longitude,Latitude,Name,Description' . PHP_EOL);
        }
        fwrite($fp, '('.$row['long'] . '),(' . $row['lat'] . '),' . $row['name'] . ',' . $row['url'] . PHP_EOL);
    
        if($rowcount % 1500 === 0) {
            fclose($fp);
            $newline = true;
        }
    }
    if($rowcount % 1500 !== 0) {
        fclose($fp);
    }
}
elseif($type === 'kml') {
    $max = 2000;
    $template = file_get_contents('./template.kml');
    
    foreach($data as $row) {
        if($newline === true) {
            $newline = false;
            $filecount++;

            $fp = fopen('export'.$filecount.'.kml', 'w');
            fwrite($fp, str_replace('###name###', 'Part'.$filecount, $template));
        }

        $rowcount++;

        $o = '<Placemark>
                <name>'.$row['name'].'</name>
                <description><![CDATA[<img src="'.$row['image'].'" height="200" width="auto" /><br><br>'.$row['url'].']]></description>
                <styleUrl>'.$row['icon'].'</styleUrl>
                <ExtendedData>
                <Data name="gx_media_links">
                    <value><![CDATA['.$row['image'].']]></value>
                </Data>
                </ExtendedData>
                <Point>
                <coordinates>
                    '.$row['lat'].','.$row['long'].',0
                </coordinates>
                </Point>
            </Placemark>
        ';

        fwrite($fp, $o);

        if($rowcount % $max === 0) {
            fwrite($fp, '</Document></kml>');
            fclose($fp);
            $newline = true;
        }
    }

    if($rowcount % $max !== 0) {
        fwrite($fp, '</Document></kml>');
        fclose($fp);
    }
}

// Write finished data 
$export_date = './export.json';
$jsonData = [
        "last_fetched" => date("d-m-Y H:i:s", time()),
];
$jsonString = json_encode($jsonData, JSON_PRETTY_PRINT);
$fp = fopen($export_date, 'w');
fwrite($fp, $jsonString);
fclose($fp);

}

if (isset($_GET['run'])) {
  createDataSet();
};

$export_date = './export.json';
$strJsonFileContents = file_get_contents($export_date);
$array = json_decode($strJsonFileContents, true);
$last_fetched = $array["last_fetched"];
echo 'Current time : ' . date("d-m-Y H:i:s", time()) . '</br>';
echo 'Last Fetched : ' . $last_fetched ;