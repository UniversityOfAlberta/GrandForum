var dateTimeHelpers = {
    formatDate: function(date, inputFormat='yyyy-MM-dd', outputFormat='d MMMM, yyyy'){
        var d = new Date();
        d.fromFormattedString(date, inputFormat);
        return d.toFormattedString(outputFormat);
    }
}
