<?php
$title = get_field('services_headline');
$lists = get_field('lists');
?>

<section class="about-services-overview"><div>

  <?php
  //title
  echo ($title ? '<h1>'.$title.'</h1>' : '');
  //lists
  if ($lists) {
    echo '<div class="lists">';
    foreach($lists as $list) {
      echo '<div class="title-and-list">';
      echo ( isset($list['title']) ? '<h3>'.$list['title'].'</h3>' : '');
      if ( isset($list['list']) ) {
        $lines = explode("\r\n",$list['list']);
        echo '<div class="list '.( (count($lines)>3) ? 'lqu-limited-list' : '').'">';
        foreach($lines as $line => $val) echo "<span>".$val."</span>";
        echo '</div>';
        if ( count($lines)>3) {
          echo '<span class="more">Show More +</span>';
        }
      }
      echo '</div>';
    }
    echo '</div>';
  }
  ?>

  <a class="contact-button show-inquire-link" href="/contact">Inquire</a>

</div></section>
