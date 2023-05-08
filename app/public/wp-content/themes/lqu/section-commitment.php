<?php
  $items = get_field('commitments','option');
?>
<section class="commitment">
  <div>

    <div class="overlay">
      <div class="images">
        <?php
        foreach ($items as $i) {
          $image = $i['image'];
          ?>
          <div
            class="image-item"
            style="background-image:url('<?=$image['sizes']['large'] ?>');"
          >
          </div>
          <?php
        }
        ?>
      </div>
      <div class="text_and_nav">
        <div class="text">
          <?php
          foreach ($items as $i) {
            ?>
            <div class="text-item">
              <span>
                <?=$i['text'] ?>
              </span>
            </div>
            <?php
          }
          ?>
        </div>
        <div class="nav">
          <?php
          for ($i=1; $i<=count($items); $i++) {
            echo '<span>'.($i<10?'0':'').$i.'</span> ';
          }
          ?>
        </div>
      </div>
    </div>

    <div class="items">
    <?php
    foreach ($items as $i) {
      $image = $i['image'];
      ?>
        <div class="item">
          <div class="image">
            <img
              src="<?=$image['sizes']['large'] ?>"
              width="<?=$image['sizes']['large-width'] ?>"
              height="<?=$image['sizes']['large-height'] ?>"
              alt="<?=$image['alt'] ?>"
            />
          </div>
          <div class="text">
            <?=$i['text'] ?>
          </div>
        </div>
      <?php
    }
    ?>
    </div>



  </div>
</section>
