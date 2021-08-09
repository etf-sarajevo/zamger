/*
 *  Search : Places ( and/or Municipalities and / or Countries )
 *
 *  To use this script, couple things must be done:
 *
 *      - set classname as placeSearch
 *      - since it is used just like select (pair of key and value), both needs to be set
 *        value = "String" - name; and input attribute idval = "Integer part" - ID
 *
 *  It would create additional input field with the same name as input + ID, placeOfBirth => placeOfBirthID
 *
 *  That means, picking up data with php would goes like $name = $_REQUEST['placeOfBirth'], $id = $_REQUEST['placeOfBirthID']
 */

let activeRequest = false;
$(".placeSearch").keyup(function () {
    let $this = $(this);
    let value = $(this).val();

    if(!activeRequest){
        activeRequest = true;
        console.clear(); // Clear console log for debugging

        if(value !== ''){
            ajax_api_start('person/place/search&resolve[]=Country', 'GET', {query : value}, function (result) {
                /** If there is any previous wrapper element, remove it! **/
                $this.parent().find('.search-options').remove();

                let wrapper = $("<div>").attr('class', 'search-options');

                for(let i=0; i<result['results'].length; i++){
                    let item = result['results'][i];

                    wrapper.append(function () {
                        return $("<div>").attr('class', 'search-value')
                            .attr('title', item['name'] + ' (' + item['Municipality']['name'] + ', ' + item['Country']['name'] + ')')
                            .attr('idVal', item['id'])
                            .append(function () {
                                return $("<p>").text(item['name'])
                            })
                            .append(function () {
                                return $("<i>").attr('class', 'far fa-check-square')
                            });
                    });

                    console.log(item);
                }

                /** Append wrapper with values to searchable area **/
                $this.parent().append(wrapper);
                activeRequest = false;
            }, function (text, status, url) {
                $.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');
            });
        }else{
            $this.parent().find('.search-options').remove();
            activeRequest = false;
        }
    }
});

/*
 *  USE VALUE
 *  On click event, use value and id and set to parent().parent() first input
 */

$("body").on('click', '.search-value', function () {
    let inputName = $(this).parent().parent().find('.placeSearch');
    let inputID   = $(this).parent().parent().find('.placeSearchID');

    inputName.val($(this).find('p').text()).attr('initname', $(this).find('p').text());

    inputID.val($(this).attr('idval')).attr('initvalue', $(this).attr('idval'));

    $(this).parent().remove();
});

/*
 *  FOCUS OUT
 *  On focus out - use init values to real values
 */

$(".placeSearch").focusout(function () {
    if($('.search-value' + ':hover').length) {
        return;
    }

    $(this).parent().find('.search-options').remove();

    $(this).val($(this).attr('initname'));
});

/*
 *  INIT VALUE
 *
 *  On document ready, set value to init value -- need for switching
 */

$(".placeSearch").each(function () {
    let $this = $(this); // $this represents each input
    $(this).attr('initName', $(this).val());

    $(this).parent().append(function () {
        return $("<input type='hidden'>").attr('name', $this.attr('name') + 'ID')
            .attr('id', $this.attr('name') + 'ID')
            .attr('value', $this.attr('idVal'))
            .attr('class', $this.attr('class') + 'ID')
            .attr('initValue', $this.attr('idVal'));
    });
});

/** Deprecated -- select-2 ajax search **/
$(".place-search").keyup(function () {
    // console.log($(this).val());
    // $("#encodings").append(function () {
    //     return $("<option>").attr('value', "Aladin").text(14)
    // }).append(function () {
    //     return $("<option>").attr('value', 15).text('Kapić')
    // });
});
