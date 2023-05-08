<section class="about-process"><div>

  <?php
  $processes = get_field('processes');
  ?>

  <?php if ( $processes and count($processes) ) { ?>
    <div class="processes">
      <?php foreach($processes as $p) { ?>
        <div class="process">

          <?php
          if ( isset($p['image']) ) {
            $image = $p['image'];
            ?>
            <div
              class="image"
              style="background-image:url('<?=$image['sizes']['large'] ?>')"
              data-img-width="<?=$image['sizes']['large-width'] ?>"
              data-img-height="<?=$image['sizes']['large-height'] ?>"
            >
              <?php the_acf_image( $p['image'] ); ?>
            </div>
            <?
          }
          ?>

          <div class="title-text-link"><div>
            <?php
            echo ( (isset($p['title']) and $p['title']) ?  '<h1>'.$p['title'].'</h1>' : '' );

            if ( (isset($p['text']) and $p['text']) ) {
              echo '<div class="text lqu-clamp">'.$p['text'].'</div>';
              echo '<div class="more">Read More</div>';
            }

            if ( isset($p['link']['url']) ) {
              echo '<a href="'.$p['link']['url'].'" >'.$p['link']['title'].'</a>';
            }
            ?>
          </div></div>



        </div>
      <?php } ?>
    </div>
  <?php } ?>

</div></section>
