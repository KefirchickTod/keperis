'use strict';

class Notes {
    constructor(e, data) {
        this.e = e;
        this.sendData = data;
    }

    creatEditElemt() {
        this.editElement = $('<div class="newPost">\n' +
            '  <div class="toolbar">\n' +
            '    <button type="button" data-func="bold"><i class="fa fa-bold"></i></button>\n' +
            '    <button type="button" data-func="italic"><i class="fa fa-italic"></i></button>\n' +
            '    <button type="button" data-func="underline"><i class="fa fa-underline"></i></button>\n' +
            '    <button type="button" data-func="justifyleft"><i class="fa fa-align-left"></i></button>\n' +
            '    <button type="button" data-func="justifycenter"><i class="fa fa-align-center"></i></button>\n' +
            '    <button type="button" data-func="justifyright"><i class="fa fa-align-right"></i></button>\n' +
            '    <button type="button" data-func="insertunorderedlist"><i class="fa fa-list-ul"></i></button>\n' +
            '    <button type="button" data-func="insertorderedlist"><i class="fa fa-list-ol"></i></button>\n' +
            '    <div class="customSelect">\n' +
            '      <select data-func="fontname">\n' +
            '        <optgroup label="Serif Fonts">\n' +
            '          <option value="Bree Serif">Bree Serif</option>\n' +
            '          <option value="Georgia">Georgia</option>\n' +
            '           <option value="Palatino Linotype">Palatino Linotype</option>\n' +
            '          <option value="Times New Roman">Times New Roman</option>\n' +
            '        </optgroup>\n' +
            '        <optgroup label="Sans Serif Fonts">\n' +
            '          <option value="Arial">Arial</option>\n' +
            '          <option value="Arial Black">Arial Black</option>\n' +
            '          <option value="Asap" selected>Asap</option>\n' +
            '          <option value="Comic Sans MS">Comic Sans MS</option>\n' +
            '          <option value="Impact">Impact</option>\n' +
            '          <option value="Lucida Sans Unicode">Lucida Sans Unicode</option>\n' +
            '          <option value="Tahoma">Tahoma</option>\n' +
            '          <option value="Trebuchet MS">Trebuchet MS</option>\n' +
            '          <option value="Verdana">Verdana</option>\n' +
            '        </optgroup>\n' +
            '        <optgroup label="Monospace Fonts">\n' +
            '          <option value="Courier New">Courier New</option>\n' +
            '          <option value="Lucida Console">Lucida Console</option>\n' +
            '        </optgroup>\n' +
            '      </select>\n' +
            '    </div>\n' +
            '    <div class="customSelect">\n' +
            '      <select data-func="formatblock">\n' +
            '        <option value="h1">Heading 1</option>\n' +
            '        <option value="h2">Heading 2</option>\n' +
            '        <option value="h4">Subtitle</option>\n' +
            '        <option value="p" selected>Paragraph</option>\n' +
            '      </select>\n' +
            '    </div>\n' +
            '  </div>\n' +
            '  <div id ="editorConteiner"></div>\n' +
            '  <div class="buttons">\n' +
            '    <!--<button type="button">save draft</button>-->\n' +
            '    <button data-func="clear" type="button">clear</button>\n' +
            '    <button data-func="save" type="button">save</button>\n' +
            '  </div>\n' +
            '</div>');
    }

    creat() {
        if ($(this.e).children("#editor").length > 0) {
            return false;
        }
        let tdObj = $(this.e);
        this.creatEditElemt();
        let preText = tdObj.html();
        let inputObj = $('<div class="editor" id = "editor" contenteditable></div>');
        this.editElement.dialog({
            modal: true,
            height: 800,
            width: 800
        });
        inputObj.attr('unselectable', 'on').select(function () {
            return false
        }).css({
            '-moz-user-select': '-moz-none',
            '-o-user-select': 'none',
            '-khtml-user-select': 'none',
            '-webkit-user-select': 'none',
            'user-select': 'none'
        });
        tdObj.html("");
        this.init();
        inputObj
            .html(preText)
            .appendTo(this.editElement.find("#editorConteiner"))
            .trigger("focus")
            .trigger("select");
        let sendData = this.sendData;
        $('button[data-func="save"]').click(function () {
            let text = $(inputObj).html();
            sendData.text = text;
            tdObj.html(text);
            api('Api', sendData.o, {
                'id': sendData.id,
                comment: sendData.text
            }, (e) => {
                console.log(e);
            }, false);
        });
        inputObj.click(function () {
            return false;
        });
    }


    creat_old() {

        let tdObj = this.e;
        let eventRegId = this.e.data('id');
        if (this.e.children("textarea").length > 0) {
            return false;
        }
        let preText = tdObj.html();
        let inputObj = $("<textarea style='resize: none; height:63px;'></textarea>");
        inputObj.attr('unselectable', 'on').select(function () {
            return false
        }).css({
            '-moz-user-select': '-moz-none',
            '-o-user-select': 'none',
            '-khtml-user-select': 'none',
            '-webkit-user-select': 'none',
            'user-select': 'none',
            'width': '-webkit-fill-available'
        });
        tdObj.html("");
        inputObj
            .css({border: "0px", fontSize: "12px"})
            .val(preText)
            .appendTo(tdObj)
            .trigger("focus")
            .trigger("select");
        let sendData = this.sendData;
        inputObj.keyup(function (event) {
            if (13 == event.which) { // press ENTER-key
                let text = inputObj.val();
                sendData.text = text;
                tdObj.html(text.replace(/\r?\n/g, ""));
                api('Api', sendData.o, {
                    'id': sendData.id,
                    comment: sendData.text
                }, (e) => {
                    console.log(e);
                    $(tdObj).scrollTop(0);
                }, false);
            } else if (27 == event.which) {  // press ESC-key
                tdObj.html(preText);
            }
        });
        inputObj.click(function () {
            return false;
        });
    }

    init() {
        let modal = this.editElement;
        $('.newPost button[data-func]').click(function () {
            document.execCommand($(this).data('func'), false);
        });
        $('.newPost select[data-func]').change(function () {
            var $value = $(this).find(':selected').val();
            document.execCommand($(this).data('func'), false, $value);
        });

        if (typeof (Storage) !== "undefined") {
            $('.editor').keypress(function () {
                $(this).find('.saved').detach();
                modal.close();

            });
            $('.editor').html(localStorage.getItem("wysiwyg"));

            $('button[data-func="clear"]').click(function () {
                $('.editor').html('');
                localStorage.removeItem("wysiwyg");
            });


        }
    }

    sendData(selector, comment){
        let func = selector.data('o');
        let id = selectr.data('i');

        api('Api', func, {
            'id': id,
            'comment':comment
        }, (e) => {

        }, false);
    }

}
