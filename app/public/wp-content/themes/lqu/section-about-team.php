<section class="about-team"><div>
  <h3>Meet the Team</h3>
  <div class="team-grid">
  <?php
  foreach ( get_field('team') as $member ) {
    ?>
    <div>
      <?php
      if ( isset($member['image']) ) {
        the_acf_image( array('image'=>$member['image'], 'tag'=>'div', 'class'=>'image') );
      }
      echo ( isset($member['name']) ?  '<div class="name">'.$member['name'].'</div>' : '' );
      echo ( isset($member['title']) ? '<div class="title">'.$member['title'].'</div>' : '' );
      echo ( isset($member['quote']) ? '<div class="quote">	&ldquo;'.$member['quote'].'&rdquo;</div>' : '' );
      ?>
    </div>
    <?
  }
  ?>
  </div>
</div></section>
