<?php
include('configure.php');

//use gitbook's SUMMARY.md to create an array that represents the book's structure
$structure = array();
$summary = file_get_contents("{$sourceDir}SUMMARY.md");
$sections = explode("\n* ", $summary);
foreach($sections as $section){
    $chapters = explode("\n", $section);
    preg_match('/.*\[(.*)\].*/', $chapters[0], $sectionTitle);
    preg_match('/.*\((.*)\/.*/', $chapters[0], $sectionPath);
    $sectionTitle=$sectionTitle[1];
    $sectionPath=$sectionPath[1];
    $structure[$sectionPath]['title']=$sectionTitle;
    unset($chapters[0]);
    foreach($chapters as $chapter){
        if(strlen($chapter)){
        preg_match('/.*\[(.*)\].*/', $chapter, $chapterTitle);
        preg_match('/.*\/(.*)\)/', $chapter, $chapterPath);
        $chapterTitle=$chapterTitle[1];
        $chapterPath=$chapterPath[1];
        $structure[$sectionPath]['chapters'][$chapterPath]=$chapterTitle;
        }
    }
}


//create a yaml file
$yf=array();
$yf[]="site_name: '{$siteName}'";
$yf[]="site_url: http://rtfd.civicrm.org";
$yf[]="repo_url: https://github.com/civicrm/civicrm-user-guide-rtfd";
$yf[]="site_description: 'A guide for users and administrators of CiviCRM'";
$yf[]="site_author: 'The CiviCRM community'";
// $yf[]="google_analytics";
$yf[]="";
$yf[]="";
$yf[]="theme: readthedocs";
$yf[]="";
$yf[]="pages:";
$yf[]="- Home: 'index.md'";
foreach($structure as $sectionPath => $section){
    $yf[]="- {$section['title']}:";
    foreach($section['chapters'] as $chapterPath => $chapterTitle) {
       $yf[]="    - '$chapterTitle': '$sectionPath/$chapterPath'";
    }
}


exec("rm -rf $destDir*");
exec("mkdir -p {$destDir}docs/img");
exec("cp {$sourceDir}images/* {$destDir}docs/img");
foreach($structure as $sectionPath => $sectionTitle){
    exec("mkdir {$destDir}docs/{$sectionPath}");
    foreach($sectionTitle['chapters'] as $chapterPath => $chapterTitle) {
        exec("cp {$sourceDir}{$sectionPath}/{$chapterPath} {$destDir}docs/{$sectionPath}/$chapterPath");
    }
}
exec("sed -i s:\(/images/:\(/img/:g {$destDir}docs/*/*.md");
file_put_contents("{$destDir}mkdocs.yml", implode($yf, "\n"));
file_put_contents("{$destDir}README.md", $readme);
file_put_contents("{$destDir}docs/index.md", $index);
