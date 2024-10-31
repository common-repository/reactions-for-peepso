function PsReactions()
{
    this.current_post = null;
    this.page_loading = false;
    // used for multiple ajax checks
    this.is_ajax_loading = [];
}

var reactions = new PsReactions();

PsReactions.prototype.action_reactions = function(elem, act_id)
{
    var $reactions_options = jQuery(".ps-js-act-reactions-options--" + act_id);

    $reactions_options.fadeToggle(200);
};

PsReactions.prototype.action_react = function(elem, act_id, post_id, react_id)
{
    if ( this.action_react_progress ) return;
    this.action_react_progress = true;

    var req = { act_id: act_id, post_id: post_id, react_id: react_id };
    var that = this;

    var $reactions_output = jQuery(".ps-js-act-reactions--" + act_id);

    // unbold all reaction options
    jQuery(".ps-reaction-option--" + act_id).removeClass('ps-reaction-option-selected');

    // bold the selected one
    jQuery(".ps-reaction-option-" + react_id +"--" + act_id).addClass('ps-reaction-option-selected');

    // hide the reactions-options box
    jQuery(".ps-js-act-reactions-options--" + act_id).fadeOut(200);

    $PeepSo.postJson("reactionsajax.react", req, function(json) {

        that.action_react_progress = false;
        if (json.success) {



            $like_a = jQuery('.ps-reaction-toggle--'+ act_id);
            $like_span = jQuery('.ps-reaction-toggle--'+ act_id+':first-child');

            $delete_a = jQuery('.ps-reaction-option-delete--' + act_id);

            $delete_a.show();

            $like_a.removeClass();
            $like_a.addClass('ps-reaction-toggle--'+act_id);
            $like_a.addClass('ps-icon-reaction');

            if( jQuery.isNumeric(json.data.reaction_mine_id) ) {
                $like_a.addClass('liked');
                $like_a.addClass(json.data.reaction_mine_class);

                $like_span.html('<span>' + json.data.reaction_mine_label + '<span>');
            }

            // update the reactions html
            if (json.data.reactions_html) {
                $reactions_output.html(json.data.reactions_html).show();
            } else {
                $reactions_output.html('').hide();
            }


        } else {
            alert('Something went wrong');
        }
    });
};

PsReactions.prototype.action_react_delete = function(elem, act_id, post_id)
{
    if ( this.action_react_progress ) return;
    this.action_react_progress = true;

    var req = { act_id: act_id, post_id: post_id };
    var that = this;

    var $reactions_output = jQuery(".ps-js-act-reactions--" + act_id);

    // unbold all reaction options
    jQuery(".ps-reaction-option--" + act_id).removeClass('ps-reaction-option-selected');

    // hide the reactions-options box
    jQuery(".ps-js-act-reactions-options--" + act_id).fadeOut(200);

    $PeepSo.postJson("reactionsajax.react_delete", req, function(json) {

        that.action_react_progress = false;
        if (json.success) {

            $like_a = jQuery('.ps-reaction-toggle--'+ act_id);
            $like_span = jQuery('.ps-reaction-toggle--'+ act_id+':first-child');

            $delete_a = jQuery('.ps-reaction-option-delete--' + act_id);

            $delete_a.hide();

            $like_a.removeClass();
            $like_a.addClass('ps-reaction-toggle--'+act_id);
            $like_a.addClass('ps-icon-reaction');

            if( !jQuery.isNumeric(json.data.reaction_mine_id) ) {
                $like_a.removeClass('liked');
                $like_a.addClass(json.data.reaction_mine_class);
                $like_span.html('<span>' + json.data.reaction_mine_label + '<span>');
            }

            // update the reactions html
            if (json.data.reactions_html) {
                $reactions_output.html(json.data.reactions_html).show();
            } else {
                $reactions_output.html('').hide();
            }
        } else {
            alert('Something went wrong');
        }
    });
};

PsReactions.prototype.action_reactions_html = function(elem, act_id) {

    if ( this.action_react_progress ) return;
    this.action_react_progress = true;

    var req = { act_id: act_id };
    var that = this;

    var $reactions_output = jQuery(".ps-js-act-reactions--" + act_id);

    $PeepSo.postJson("reactionsajax.reactions_html", req, function(json) {

        that.action_react_progress = false;
        if (json.success) {
            $reactions_output.html(json.data.reactions_html);
        } else {
            alert('Something went wrong');
        }
    });
}

PsReactions.prototype.action_reactions_html_details = function(elem, act_id, post_id) {

    if ( this.action_react_progress ) return;
    this.action_react_progress = true;

    var req = { act_id: act_id };
    var that = this;

    var $reactions_output = jQuery(".ps-js-act-reactions--" + act_id);

    $PeepSo.postJson("reactionsajax.reactions_html_details", req, function(json) {

        that.action_react_progress = false;
        if (json.success) {
            $reactions_output.html(json.data.reactions_html);
        } else {
            alert('Something went wrong');
        }
    });
}