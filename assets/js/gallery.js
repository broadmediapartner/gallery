(function($){

    var Gallery = {

        init: function(){

            $('.go-gallery').each(function(){
                new Gallery.Instance(this);
            });

        },

        Instance: function(element){

            var instance = this;

            this.container = $(element);
            this.id = this.container.attr('id');
            this.list = this.container.find('ul.go-gallery-list');
            this.gap = this.container.data('gap');
            this.borderSize = this.container.data('border-size');
            this.lightbox = this.container.data('lightbox');
            this.borderColor = this.container.data('border-color');
            this.menuColor = this.container.data('menu-color');
            this.menuBg = this.container.data('menu-bg');
            this.menuGap = this.container.data('menu-gap');
            this.bg = this.container.data('bg');
            this.overlayColor = this.container.data('overlay-color');
            this.descColor = this.container.data('desc-color');
            this.filters = this.container.find('ul.go-gallery-filters');
            this.items = $('li', this.container);
            this.win = $(window);
            this.overlay = $('<div>').attr('id', 'go-overlay');

            this.overlayBg = $('<div>').attr('id', 'go-overlay-bg');


            this.overlay.append(this.overlayBg);
            $('body').append(this.overlay);

            this.init = function(){

                console.log(this.container);

                this.prepareStyle();


                this.list.isotope({
                    itemSelector: '.go-gallery-item',
                    layoutMode: 'masonry'
                });

                this.filters.find('a').on('click', this.filter.bind(this));


                if(this.container.hasClass('style-squared')){

                    var item, figure, image;

                    this.items.each(function(){
                        item = $(this);
                        image = $('<div>').addClass('image').css('background-image', 'url(' + item.find('img').attr('src') + ')');
                        item.find('figure').append(image);
                    });

                }

                //this.items.on('click', this.click.bind(this));
                //this.overlay.on('click', this.overlayClick.bind(this));

                if(this.lightbox == 'yes'){
                    $('a.image-wrap', this.container).tosrus();
                }

            };

            this.getWindowSize = function(){

                return {
                    width: this.win.innerWidth(),
                    height: this.win.innerHeight()
                };

            };

            this.overlayClick = function(event){

                this.overlay.hide();

            };

            this.loadImage = function(source, callback){

                var image = new Image();
                image.onload = callback;
                image.src = source;

            };

            this.click = function(event){

                event.stopPropagation();

                var item = $(event.currentTarget);
                var figure = item.find('figure');
                var source = item.data('source');
                var offset = figure.offset();

                var win = this.getWindowSize();
                var width = figure.width();
                var height = figure.height();

                console.log(win);
                console.log(width);
                console.log(height);

                var size = {};
                size.top = offset.top - scrollY;
                size.left = offset.left - scrollX;
                size.bottom = win.height - height - size.top;
                size.right = win.width - width - size.left;

                this.loadImage(source, function(){

                    this.overlay.css(size);

                    this.overlayBg.css({
                        backgroundImage: 'url(' +source  + ')',
                        width: win.width,
                        height: win.height
                    });

                    this.overlay.show();

                    this.overlay.animate({left: 0, right: 0}, 500, function(){
                        this.overlay.animate({top: 0, bottom: 0}, 500, function(){

                        }.bind(this));
                    }.bind(this));

                }.bind(this));

            };

            this.filter = function(event){

                event.stopPropagation();

                var link = $(event.target);
                var category = link.data('filter');

                if(category != ''){
                    this.list.isotope({filter: '.category-' + category.toLowerCase()});
                }
                else{
                    this.list.isotope({filter: ''});
                }

                return false;

            };

            this.prepareStyle = function(){

                var style = $('<style>');
                var prefix = '#' + this.id;

                style.html([
                    prefix + ' ul.go-gallery-list li.go-gallery-item {',
                    'padding: ' + (this.gap / 2) + 'px;',
                    '}',
                    prefix + ' ul.go-gallery-list li.go-gallery-item .image-wrap {',
                    'padding: ' + this.borderSize + 'px;',
                    'background: ' + this.borderColor + ';',
                    '}',
                    prefix + ' ul.go-gallery-list li.go-gallery-item .image-overlay {',
                    'background: ' + this.overlayColor + ';',
                    '}',
                    prefix + ' ul.go-gallery-list li.go-gallery-item .image-overlay h4 {',
                    'color: ' + this.descColor + ';',
                    '}',
                    prefix + ' {',
                    'background-color: ' + this.bg + ';',
                    '}',
                    prefix + ' ul.go-gallery-filters li a {',
                    'background-color: ' + this.menuBg + ';',
                    'color: ' + this.menuColor + ';',
                    'margin: 0 ' + (this.menuGap / 2) + 'px;',
                    '}'
                ].join(''));

                $(document.head).append(style);

                this.list.css('margin', this.gap / 2);

            };

            imagesLoaded(this.container.get(0), this.init.bind(this));

        }

    };


    $(document).ready(Gallery.init);


})(jQuery);