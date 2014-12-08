/**
 * Created by ivlasenko on 08.12.14.
 */
;
String.prototype.highlight = function(needle, color){
    color = color || '#00b3ee';
    var tag_open = "<span style='background-color: "+color+"'>";
    var tag_close = "</span>";
    return this.replace(new RegExp(needle,'g'),tag_open+needle+tag_close);
}
var APP = {
    loadData : function(url, callback, $table){
        $.ajax({
            type: "POST",
            url: url,
            async:false,
            dataType:'json',
            success: function(data){
                console.log(data);
                callback(data, $table);

            },
            error:function(){
                alert("Can't perform request");
            }
        });
    },
    drawTable : function(data, $table){
        var head_data = data[0];

        //draw head
        var head = ['<thead>', '<tr>']; //open tags
        for (var i in head_data){
            console.log(i);
            head.push('<th>'+i+'</th>');
        }
        head.push('</tr>');   //close
        head.push('</thead>');//tags
        $table.append(head.join(''));

        //draw data

        $table.append('<tbody></tbody>');
        data.forEach(function(user){
            var tr = ['<tr>']; //open tags
            for (var i in user){

                tr.push('<td data-value="'+user[i]+'">'+user[i]+'</td>');
            }
            tr.push('</tr>');   //close tag


            $table.find('tbody').append(tr.join(''));
        });
    }
};

$(function (){

    APP.loadData('data.json', APP.drawTable, $('.table'));

    // search in the table
    $('#search_input').keyup(function(){
        $('.table tbody tr').show();
        var $this = $(this);

        var needle = $this.val();

        //find string in any td of the table
        $('.table tbody td').each(function(){
            var $td = $(this);

            var value = ''+$td.data('value');
            if (value.indexOf(needle) === -1 || needle === ''){
                $td.removeClass('found');
                $td.html( value );

            } else{

                $td.html(value.highlight(needle));
                $td.addClass('found');
            }
        });


        $('#hide').trigger('change');

    });

    $('#hide').change(function(){
        if ($('#search_input').val()==''){return}

        if ($(this).prop('checked') == true){
            $('.table tbody tr').each(function(){
                if ( $(this).find('.found').length == 0 ){
                    $(this).hide();
                }
            });

        } else {
            $('.table tbody tr').each(function(){
                if ( $(this).find('.found').length == 0 ){
                    $(this).show();
                }
            });
        }
    });

});