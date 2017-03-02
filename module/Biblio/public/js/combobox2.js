/*function add_form_element(elementName) {
    var selectDiv = document.getElementById(elementName);
    var input = selectDiv.children[0];
    var newInput = input.cloneNode(true);
    selectDiv.appendChild(newInput);

    return false;
}*/

function add_form_element(elementName) {
    var selectDiv = document.getElementById(elementName);
    var input = selectDiv.children[0].children[0].children[1];
    //input.children[1].setAttribute('name', elementName + "[]");
    var newInput = input.cloneNode(true);
    newInput.removeChild(newInput.lastChild)
    window.selectNr++;
    var newInputId = newInput.children[1].id.slice(0, -1) + window.selectNr;
    newInput.children[1].id = newInputId;
    selectDiv.children[0].children[0].appendChild(newInput);
    $("#" + newInputId).combobox();
    return false;
}

function addoptions(selectbox) {
    console.log("Add options");
    console.log(selectbox);
    var option = $("<option>")
    //option.attr("value", "a");
    //option.innerHTML("test 1");
    //selectbox.options = option.insertAfter(selectbox);
    text = "A";
    value = "a";
    var option1 = new Option(text, value)
    //console.log(selectbox);
    //console.log(option1);
    selectbox.append(option1);
}

function addoption(selectbox, value, text) {
    var option = new Option(text, value);
    selectbox.append(option);
}

function getoptions(selectbox, optionType, selectedValue = false) {
    var options = $.ajax({
        url:'/biblio/optionsAjax',
        type: 'POST',
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        headers: {
            Accept : "application/json; charset=utf-8",
        },
        data: {option_type: optionType},
        success: function (data, status) {
            //var options3 = data;
            console.log('get options')
            console.log(selectbox);
            //selectbox.find('option').empty();
            //console.log(selectbox);
            $.each(data, function(key, value) {
                //console.log("ID: " + value[0] + "; Name: " + value[1]);
                addoption(selectbox, value[0], value[1]);
            })
            if (selectedValue) {
                selectbox.children('option')[selectedValue-1].selected = "true"
            }
            console.log("options have been loaded...")
            //console.log(data);
            //console.log(status);
            return data;
        },
        error: function(xhr, desc, err) {
            console.log(xhr);
            console.log("Details: " + desc + "\nError:" + err);
        }
    });
    //console.log(options);
    //return options;
}

function createOption(selectbox, optionType, newOption) {
    $.ajax({
        url: '/biblio/newOptionAjax',
        type: 'POST',
        data: {option_type: optionType, new_option: newOption},
        dataType: 'json',
        success: function (data, status) {
            console.log(data);
            console.log(status);
            selectbox.children('option').remove();
            getoptions(selectbox, data.option_type, data.new_option_id);
            //selectbox.val(data.new_option);
            // TODO: set newOptionId (=data) as selected value
            return data.new_option_id;
        },
        error: function (xhr, desc, err) {
            console.log(xhr);
            console.log("Details: " + desc + "\nError:" + err);
        }
    });
}

$( function() {
    $.widget( "custom.combobox", {
        _create: function() {
            this.wrapper = $( "<span>" )
                .addClass( "custom-combobox" )
                .insertAfter( this.element );
            //console.log(this.element)
            this.element.hide();
            var inputName = this.element[0].id;
            this.element[0].setAttribute('name', inputName + "[]");
            window.selectNr = 1;
            this.element[0].setAttribute('id', this.element[0].id + window.selectNr);
            getoptions(this.element, inputName);
            this._createAutocomplete();
            //this._createShowAllButton();
        },

        _createAutocomplete: function() {
            var selected = this.element.children( ":selected" ),
                value = selected.val() ? selected.text() : "";

            this.input = $( "<input>" )
                .appendTo( this.wrapper )
                .val( value )
                .attr( "title", "" )
                .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: $.proxy( this, "_source" )
                })
                .tooltip({
                    classes: {
                        "ui-tooltip": "ui-state-highlight"
                    }
                });

            this._on( this.input, {
                autocompleteselect: function( event, ui ) {
                    ui.item.option.selected = true;
                    this._trigger( "select", event, {
                        item: ui.item.option
                    });
                    console.log(ui.item.option.innerHTML) // author id: ui.item.option.value , author name: ui.item.option.innerHTML
                },

                autocompletechange: "_removeIfInvalid"
            });
        },

        _createShowAllButton: function() {
            var input = this.input,
                wasOpen = false;

            $( "<a>" )
                .attr( "tabIndex", -1 )
                .attr( "title", "Show All Items" )
                .tooltip()
                .appendTo( this.wrapper )
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false
                })
                .removeClass( "ui-corner-all" )
                .addClass( "custom-combobox-toggle ui-corner-right" )
                .on( "mousedown", function() {
                    wasOpen = input.autocomplete( "widget" ).is( ":visible" );
                })
                .on( "click", function() {
                    input.trigger( "focus" );

                    // Close if already visible
                    if ( wasOpen ) {
                        return;
                    }

                    // Pass empty string as value to search for, displaying all results
                    input.autocomplete( "search", "" );
                });
        },

        _source: function( request, response ) {
            var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
            response( this.element.children( "option" ).map(function() {
                var text = $( this ).text();
                if ( this.value && ( !request.term || matcher.test(text) ) )
                    return {
                        label: text,
                        value: text,
                        option: this
                    };
            }) );
        },

        _removeIfInvalid: function( event, ui ) {

            // Selected an item, nothing to do
            if ( ui.item ) {
                return;
            }

            // Search for a match (case-insensitive)
            var value = this.input.val(),
                valueLowerCase = value.toLowerCase(),
                valid = false;
            this.element.children( "option" ).each(function() {
                if ( $( this ).text().toLowerCase() === valueLowerCase ) {
                    this.selected = valid = true;
                    return false;
                }
            });

            // Found a match, nothing to do
            if ( valid ) {
                return;
            }

            // Remove invalid value
            for (i = 0, size=this.element[0].classList.length; i<size; i++) {
                if (this.element[0].classList[i] == 'combobox') {
                    var comboboxCreateEnabled = true;
                    var comboMessage = " will be added to the list";
                    break;
                }
                if (this.element[0].classList[i] == 'combobox-get') {
                    var comboboxCreateEnabled = false;
                    var comboMessage = " is not an available option"
                    break;
                }
            }
            this.input
            //.val( "new author" )
                .attr( "title", value +  comboMessage)
                .tooltip( "open" );
            this.element.val( "" );
            this._delay(function() {
                this.input.tooltip( "close" ).attr( "title", "" );
            }, 2500 );
            var newOption = this.input.val(); // put AJAX call here to add new author here
            console.log('Trial');
            console.log(this.element);
            if (comboboxCreateEnabled) {
                // TODO: replace 2nd param in createOption (regex function?) or this will break if the 10th author is new
                var newOptionId = createOption(this.element, this.element[0].id.slice(0, -1), newOption);
            }
            this.element.children( "option" ).each(function() {
                console.log(this);
                if ($(this).text().toLowerCase() === valueLowerCase) {
                    this.selected = valid = true;
                    this.input.autocomplete("instance").term = "";
                }
            });
        },

        _destroy: function() {
            this.wrapper.remove();
            this.element.show();
        }
    });


    /*$( "#combobox" ).combobox();
    $( "#toggle" ).on( "click", function() {
        $( "#combobox" ).toggle();
    });
    $( "#test" ).on("click", testajax);
*/
    //getoptions($("#combobox"));
    //$("#combobox").combobox();
    $(".combobox").combobox();
    $(".combobox-get").combobox();
} );