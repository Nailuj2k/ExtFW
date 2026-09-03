$(function() {     

    var tab_visible = 'groups';

    $('#button-users-groups').click(function(){
        if( tab_visible == 'groups'){
            $('#tab-areas_apps_groups').slideUp(500);
            setTimeout(function(){$('#tab-areas_apps_users').slideDown();},500);
            $(this).html('Grupos / <b>Usuarios</b>');
            tab_visible = 'users';
        }else{
            $('#tab-areas_apps_users').slideUp(500);
            setTimeout(function(){$('#tab-areas_apps_groups').slideDown();},500);
            $(this).html('<b>Grupos</b> / Usuarios');
            tab_visible = 'groups';
        }
    // if ($('#tab-areas_apps_users').is(":visible")) $('#tab-areas_apps_users').slideUp(); else $('#tab-areas_apps_users').slideDown();
    // if ($('#tab-areas_apps_groups').is(":visible")) $('#tab-areas_apps_groups').slideUp(); else $('#tab-areas_apps_groups').slideDown();
    });
    $('#a_tab_AREA_USRS,#a_tab_AREA_GRPS').click(function(){
        $('#button-users-groups').css('display','none'); // .hide('fast');
    });
    $('#a_tab_AREA_APPS').click(function(){
        $('#button-users-groups').css('display','inline-block'); //.show('fast');
    });

});