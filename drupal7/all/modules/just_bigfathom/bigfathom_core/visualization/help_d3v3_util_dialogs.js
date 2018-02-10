/*
 * Some helpers for our dialog box visualizations
 *  
 * Copyright Room4me.com Software LLC 2015
 */

if (typeof bigfathom_util === 'undefined') 
{
    //Create the main object because it does not already exist
    var bigfathom_util = {};
    console.log("Created bigfathom_util");
}
if(!bigfathom_util.hasOwnProperty("dialogs"))
{
    //Create the object property because it does not already exist
    bigfathom_util.dialogs = {version: "20151110"};
}

/**
 * Create a small dialog box with a submit button
 * @param {string} dialog_name name is of the dialog
 * @param {string} dialog_anchor_id is where we place the dialog
 * @param {properties} initial_values is object of initial values for the input fields
 * @param {string} submit_button_label is text to show on the submit button
 * @param {object} save_function is handle to function we call to save the values
 * @returns {undefined}
 */
bigfathom_util.dialogs.createInputDialog = function (dialog_name, dialog_anchor_id, initial_values, submit_button_label, save_function)
{
    alert("TODO create a simple dialog called " + dialog_name + " located at position of " + dialog_anchor_id + " with values " + JSON.stringify(initial_values));
};

