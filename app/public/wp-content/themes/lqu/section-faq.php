<section class="faq"><div>
  <h1>FAQ</h1>
  <div class="faq">
    <?php
    $faqs = get_field('faqs');
    foreach ($faqs as $faq) {
      if ( isset($faq['q']) and isset($faq['a']) ) {
        ?>
        <div class="item">
          <div class="question">
            <div class="left">Q</div>
            <div class="right"><?=$faq['q'] ?></div>
          </div>
          <div class="answer">
            <div class="left answer-prompt">A</div>
            <div class="right answer-text"><?=$faq['a'] ?></div>
            <div class="right show-more-link"><em>Show More</em> +</div>
          </div>
        </div>
        <?php
      }
    }
    ?>
  </div>
</div></section>
