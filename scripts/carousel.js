function createCarousel(selector, roles){
    $(document).ready(function(){
        var people = new People();
        people.roles = roles;
        
        var view = new CarouselView({el: selector, model: people});
    });
}
