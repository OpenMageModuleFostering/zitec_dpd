/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


var zitecFieldLengths = zitecFieldLengths || {};

zitecFieldLengths.Validator = Class.create({
    initialize: function(className, fields, message, maxLength, minLength) {
        this.className =  typeof className !=='undefined'? className : 'zitec_fieldlengths-validator';
        this.fields = typeof fields !=='undefined'? fields : [];
        this.message = typeof message !=='undefined'? message : "Too long";
        this.maxLength = typeof maxLength !=='undefined'? maxLength : 0;
        this.minLength = typeof minLength !=='undefined'? minLength : 0;
        
        if (!this.className || !this.fields || !this.maxLength) {
            return;
        }
        
        var fixedLength = Math.floor(this.maxLength / this.fields.length);
        if (fixedLength >= this.minLength) {
            for (var i = this.fields.length - 1; i >= 0; i -= 1) {
                field = this.fields[i];
                field.writeAttribute("maxlength", fixedLength);
            }
        }
        
        if (!Validation) {
            return;
        }
        

        Validation.add(this.className, this.message, function(value, elm) {
            var fullTextLength = 0;
            var lastFieldWithContent = null;
            for (var i = this.fields.length - 1; i >= 0; i -= 1) {
                if (this.fields[i].getValue()) {
                    fullTextLength += this.fields[i].getValue().length;
                    if (!lastFieldWithContent) {
                        lastFieldWithContent = this.fields[i];
                    }
                }
            }
            
            return fullTextLength <= this.maxLength || (lastFieldWithContent && lastFieldWithContent.id != elm.id);
        }.bind(this));



        for (var i = this.fields.length - 1; i >= 0; i -= 1) {
            field = this.fields[i];
            field.addClassName(this.className)
        }
    }
    
    
}) ;
