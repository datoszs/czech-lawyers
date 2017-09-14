$(function(){
    $('.advocate-autocomplete').selectize({
        valueField: 'id_advocate',
        labelField: 'fullname',
        searchField: ['fullname', 'identification_number'],
        multiple: false,
        maximumSelectionSize: 1,
        highlight: false,
        create: false,
        render: {
            option: function(item, escape) {
                if (item.identification_number) {
                    return '<div>' + escape(item.fullname) + ' (IČ ' + item.identification_number + ')</div>';
                }
                return '<div>' + escape(item.fullname) + '</div>';
            }
        },
        load: function(query, callback) {
            if (!query.length) return callback();
            $.ajax({
                url: '/api/advocate/autocomplete/' + encodeURIComponent(query),
                type: 'GET',
                error: function() {
                    callback();
                },
                success: function(res) {
                    callback(res);
                }
            });
        }
    });
});
