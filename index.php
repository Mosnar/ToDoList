<?php
/**
 * User: Ransom Roberson
 * Date: 1/29/14
 * Time: 10:33 PM
 */
/*
// Environment setup
require_once("bootstrap.php");

// Interfaces
require_once("interfaces/DomainModel.php");
require_once("interfaces/Hasher.php");

// Core libraries
require_once("lib/DI.php");
require_once("lib/Database.php");
require_once("lib/MD5IdentityHasher.php");
require_once("lib/DateMaker.php");
// Exceptions

// Models
require_once("models/ToDoItem.php");

// Controllers
require_once("controllers/ToDoItemController.php");

// Dependency Injection Mapping
DI::mapClassAsSingleton("database", "Database");
DI::mapClass("hasher", "MD5IdentityHasher");
DI::mapClass("datemaker", "DateMaker");
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>House List</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">


    <script src="https://code.jquery.com/jquery.js"></script>

    <script type="text/javascript">
        $(function(){
            $.ajax({
                type: "POST",
                url: "ItemControllerEndpoint.php",
                data: { action: "list" },
                success: function(data){
                    data.forEach(function(task){
                        $("#myItems").append("<li>"+task.text+"</li>");
                    });
                },
                dataType: "json"
            });
        });

        $(function(){
            $("#addItemForm").submit(function( event ) {
                var text = $("#taskText").val();
                var progress = $('#inProgress').is(":checked") ? 1 : 0;
                $.ajax({
                    type: "POST",
                    url: "ItemControllerEndpoint.php",
                    data: { action: "add", inProgress: progress, text: text},
                    success: function(data){
                        $("#myItems").append("<li>"+text+"</li>");
                        $('#addItem').modal('hide');
                        $('#taskText').val("");
                        console.log(data);
                    },
                    dataType: "json"
                });
                event.preventDefault();
            });
        });
    </script>
</head>
<body>

<!-- Wrap all page content here -->
<div id="wrap">

    <!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html">House List</a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="#" data-toggle="modal" data-target="#addItem">Add Item</a></li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </div>

    <!-- Begin page content -->
    <div class="container">

        <ul id="myItems">

        </ul>


    </div>
</div>

<div id="footer">
    <div class="container">
        <p class="text-muted">&copy; 2014 Ransom Roberson</p>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addItem" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Add Item</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" role="form" id="addItemForm">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Task</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="taskText" placeholder="Description">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="inProgress"> In Progress
                                </label>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script src="js/bootstrap.min.js"></script>
</body>
</html>