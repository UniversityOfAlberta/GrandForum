dateTimeHelpers = {
    formatDate: function(date, inputFormat, outputFormat){
        inputFormat = typeof inputFormat !== 'undefined' ? inputFormat : 'yyyy-MM-dd';
        outputFormat = typeof outputFormat !== 'undefined' ? outputFormat : 'd MMMM, yyyy';
        var d = new Date();
        d.fromFormattedString(date, inputFormat);
        return d.toFormattedString(outputFormat);
    }
}
