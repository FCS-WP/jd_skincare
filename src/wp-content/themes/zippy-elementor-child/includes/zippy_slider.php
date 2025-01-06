<?php

function home_slider_shortcode($atts)
{
    wp_enqueue_style('slick-slider-css', THEME_URL . '-child' . '/assets/libs/slick/slick.css');
    wp_enqueue_style('slick-theme-css', THEME_URL . '-child' . '/assets/libs/slick/slick-theme.css');
    wp_enqueue_script('slick-slider-js', THEME_URL . '-child' . '/assets/libs/slick/slick.min.js', array('jquery'), null, true);

    wp_add_inline_script('slick-slider-js', "
        jQuery(document).ready(function($) {
            $('.project-slide').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: false,
                infinite: true,
            });
            $('.custom-prev-btn').on('click', function() {
                $('.project-slide').slick('slickPrev');
            });

            $('.custom-next-btn').on('click', function() {
                $('.project-slide').slick('slickNext');
            });
        });
    ");


    if (!is_page()) {
        return null;
    }
    $banner_sliders = get_field('banner_slider');
    if (!$banner_sliders) {
        return '<p>No images found in the gallery.</p>';
    }
    $icon_url = THEME_URL . '-child' . '/assets/icons/';
    $totalSlide = count($banner_sliders);  
    
    echo '<div class="project-slide position-relative">';
            foreach ($banner_sliders as $key => $slider) {
                ?>
                <div class="slide-item item-background" style="background-image: url('<?php echo $slider['background']['url'] ?>');">
                    <div class="card-info">
                        <div class="content-box">
                            <div class="box-header">
                                <span><?php echo ($key + 1 . '/' . $totalSlide )?></span>
                            </div>
                            <div class="box-body">
                                <div>
                                    <h2 class="content-title"><?php echo $slider['title'] ?></h2>
                                    <p class="content-description"><?php echo $slider['description'] ?></p>
                                </div>
                                <div>
                                    <a href="<?php echo $slider['link_to'] ?>" title="<?php echo $slider['title'] ?>">
                                        <button class="action-button"><?php echo $slider['button_text'] ?></button>
                                    </a>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button class="custom-prev-btn">
                                    <img src="<?php echo $icon_url. 'prev.svg' ?>" alt="Prev" />
                                </button>
                                <button class="custom-next-btn">
                                    <img src="<?php echo $icon_url. 'next.svg' ?>" alt="Next" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
    echo '</div>';
}

add_shortcode('home_slider', 'home_slider_shortcode');
