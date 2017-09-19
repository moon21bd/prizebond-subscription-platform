<?php

function getScriptCommonParam() {
    ?>
    var api_key = $("#api_key").val();
    var device_uuid = $("#device_uuid").val();
    var device_density = $("#device_density").val();
    var device_width = $("#device_width").val();
    var device_height = $("#device_height").val();
    var package_name = $("#package_name").val();
    var debug_hash_key = $("#debug_hash_key").val();
    var release_hash_key = $("#release_hash_key").val();
    <?php
}

function getScriptCommonParamForPush() {
    ?>
    var api_key = $("#api_key").val();
    var device_uuid = $("#device_uuid").val();
    <?php
}

function getCommonParamsForSendPush() {
    ?>
    <label>api_key : </label>
    <span><input type="text" id="api_key" placeholder="Enter API Key" /> (Required)
    </span><br/>
    <label>device_uuid : </label>
    <span><input type="text" id="device_uuid" value=""/>
    </span><br/>
    <?php
}

function getCommonParams() {
    ?>
    <label>api_key : </label>
    <span><input type="text" id="api_key" name="api_key" placeholder="Enter API Key" /> (Required)
    </span><br/>

    <label>device_uuid : </label>
    <span><input type="text" id="device_uuid" name="device_uuid" value="uuid"/> (Required)
    </span><br/>

    <label>device_density : </label>
    <span><input type="text" id="device_density" name="device_density" value="density"/> (Required)
    </span><br/>

    <label>device_width : </label>
    <span>
        <input type="text" id="device_width" name="device_width" value="480"/> (Required)
    </span><br>

    <label>device_height : </label>
    <span>
        <input type="text" id="device_height" name="device_height" value="480" /> (Required)
    </span><br>

    <?php
}

function getAPICommonParams() {
    ?>
    <label>device_uuid : </label>
    <span><input type="text" id="device_uuid" value="uuid"/> (Required)
    </span><br/>

    <label>package_name : </label>
    <span><input type="text" id="package_name" value=""/>(Optinal but required only production mode)
    </span><br/>

    <label>debug_hash_key : </label>
    <span><input type="text" id="debug_hash_key" value=""/>(Optinal)
    </span><br/>

    <label>release_hash_key : </label>
    <span><input type="text" id="release_hash_key" value=""/>(Optinal)
    </span><br/>

    <?php
}
?>