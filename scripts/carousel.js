function createCarousel(selector, roles){
    $(document).ready(function(){
        var people = new People();
        people.roles = roles;
        
        var view = new CarouselView({el: selector, model: people});
    });
}

function createPosterCarousel(selector, posters){
    $(document).ready(function(){
        var products = new Products(posters);
        var view = new PosterCarouselView({el: selector, model: products});
    });
}
