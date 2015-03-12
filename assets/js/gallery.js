(function($){

    var Gallery = {

        init: function(){

            $('.bmp-gallery').each(function(){
                new Gallery.Instance(this);
            });

        },

        Instance: function(element){

            var instance = this;

            this.container = $(element);
            this.list = this.container.find('ul.bmp-gallery-list');
            this.gap = this.container.data('gap');
            this.filters = this.container.find('ul.bmp-gallery-filters');
            this.items = $('li', this.container);

            this.init = function(){

                console.log(this.container);

                this.prepareStyle();


                this.list.isotope({
                    itemSelector: '.bmp-gallery-item',
                    layoutMode: 'masonry'
                });

                this.filters.find('a').on('click', this.filter.bind(this));

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

                style.html([
                    'div.bmp-gallery ul.bmp-gallery-list li.bmp-gallery-item {',
                    'padding: ' + (this.gap / 2) + 'px;',
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