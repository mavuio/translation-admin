<div id='translation-admin' ng-init='init({{$settingsAsJson}})' ng-controller='TranslationAdminController'>


    <div class='main-outer'>


        <div class='main-container'>



            <div>&nbsp;</div>
            <h1>Texte übersetzen</h1>
            <div>&nbsp;</div>

            <div translation-admin-index>


            </div>
        </div>
    </div>


    <script>
            angular.bootstrap(document.getElementById("translation-admin"),['translation-admin']);
    </script>


</div>
