var PostcodeAutocompleter = new Class.create(Ajax.Autocompleter, {
    getUpdatedChoices: function() {
        this.startIndicator();
        var entry = encodeURIComponent(this.options.paramName) + '=' +
            encodeURIComponent(this.getToken());

        this.options.parameters = this.options.callback ?
            this.options.callback(this.element, entry) : entry;

        if(this.options.defaultParams)
            this.options.parameters += '&' + this.options.defaultParams;
        this.options.parameters = $('edit_form').serialize();
        console.log(this.options);
        new Ajax.Request(this.url, this.options);
    }
});