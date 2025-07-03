CategoryButtonsView = Backbone.View.extend({
    parent: null,
    template: _.template($('#category_buttons_template').html()),

    initialize: function (options) {
        this.parent = options.parent;
    },

    events: {
    },

    render: function () {
        this.el.innerHTML = this.template();
        return this.$el;
    }

});
