<?php 

const ROUTEMAP = [
    "/" => "Start",
    "/account/login" => "Account",
    "/account/register" => "Account",
    "/account/logout" => "Logout",
    "/api/profilepicture/{id}" => "AssetManager",
    "/extremedemons" => "GDList",
    "/ninecircles" => "GDList",
    "/api/records/submit" => "SubmitRecord",
    "/account/savefiles" => "SaveFileManager",
    "/api/account/savefiles/upload" => "SaveFileManager",
    "/api/account/savefiles/delete" => "SaveFileManager",
    "/api/account/savefiles/setactive" => "SaveFileManager",
    "/api/account/savefiles/rename" => "SaveFileManager",
    "/api/account/savefiles/updaterecords" => "SaveFileManager",
    "/api/account/savefiles/deleteimported" => "SaveFileManager",
];