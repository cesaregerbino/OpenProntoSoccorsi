<?php
  $html = file_get_contents("https://www.galliera.it/118");

  $dom = new DOMDocument();
  $dom->loadHTML($html);
  $finder = new DOMXPath($dom);

  // find all divs class row
  $rows = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' row ')]");

  $data = array();
  foreach ($rows as $row) {
      $groupName = $row->getElementsByTagName('h2')->item(0)->textContent;
      $data[$groupName] = array();

      // find all div class box
      $boxes = $finder->query("./*[contains(concat(' ', normalize-space(@class), ' '), ' box ')]", $row);
      foreach ($boxes as $box) {
          $subgroupName = $box->getElementsByTagName('h3')->item(0)->textContent;
          $data[$groupName][$subgroupName] = array();

          $listItems = $box->getElementsByTagName('li');
          foreach ($listItems as $k => $li) {

              $class = $li->getAttribute('class');
              $text = $li->textContent;

              if (!strlen(trim($text))) {
                  // this should be the graph bar so kip it
                  continue;
              }

              // I see only integer numbers so I cast to int, otherwise you can change the type or event not cast it
              $data[$groupName][$subgroupName][] = array('type' => $class, 'value' => (int) $text);
          }
      }
  }

  echo '<pre>' . print_r($data, true) . '</pre>';
?>
