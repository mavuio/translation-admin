angular.module("translation-admin").directive "translationAdminItem", ->
  restrict: "A"
  replace: true
  templateUrl: '/packages/werkzeugh/translation-admin/ng/translation-admin/partials/translation-admin-item.html'
  scope: true
  link: (scope, element, attrs) ->

  controller: [ "$scope", "$element", "$attrs", "$timeout", "$transclude", "$http", "$q", ($scope, $element, $attrs, $timeout, $transclude, $http, $q) ->

    $scope.$on 'expand_item', (event,id)->
      #find item
      item=$scope.getItemForId(id)
      if item && !item.expanded
        $scope.toggleItem(item)
        console.log "expand event on ", id,item if window.console and console.log


    $scope.tinymceOptions =
      menubar: false
      plugins: [ "code fullscreen contextmenu autoresize link" ]
      toolbar1: "bold italic | undo redo | link unlink | removeformat | code fullscreen",
      relative_urls : false
      autoresize_min_height:100
      autoresize_max_height:600
      forced_root_block : false


    $scope.toggleItem = (item, e) ->
      newval = (if item.expanded then false else true)
      if $scope.app.currentExpandedItem and $scope.app.currentExpandedItem isnt item
        $scope.app.currentExpandedItem.expanded = false
        $scope.app.currentExpandedItem = null

      if newval is true
        if typeof item.data is "undefined"
          item.record1={loading:true}
          item.record2={loading:true}
          $scope.loadDetailsForItem(item.lang1_id,item.lang2_id).then (itemdata) ->
            item.record1=itemdata.record1;
            item.record2=itemdata.record2;

        $scope.app.currentExpandedItem = item
        $scope.updateUrl hash:item.lang1_id
        $timeout(
          ()->
            angular.element('.savebutton').focus()
          200
         )
      item.expanded = newval

      if $scope.app.currentExpandedItem and not $scope.app.currentExpandedItem.expanded
        $scope.updateUrl path:'empty'

    $scope.saveItem = (item,idx) ->
      $scope.saveDetailsForItem(item.record1,item.record2).then (responsedata)->
        angular.copy(responsedata.item,item);
        $element.closest('div.editrow').slideUp 500, ()->

    $scope.saveDetailsForItem = (record1,record2) ->
      $http.post($scope.settings.baseUrl + "/ng-save-item",
       record1:record1
       record2:record2
       query:$scope.query
      ).then (response) ->
        if(response.data and response.data.status is 'ok')
           return response.data
        else
          return $q.reject('cannot save details for location');
        # item.data.safedescription = $sce.trustAsHtml(item.data.description)
        # $scope.updateQueryFromUrl(); #trigger opening of showtime-popup


    # $scope.setItemval = (type,id,fieldname,value) ->
    #   $http.post($scope.settings.baseUrl + "ng-set-itemval",
    #     type:type
    #     id:id
    #     fieldname:fieldname
    #     value:value
    #   ).then (response) ->
    #     console.log "setItemval" , response  if window.console and console.log
    #     if(response.data and response.data.status is 'ok')
    #        return response
    #     else
    #        return $q.reject('cannot set value on object');


    $scope.loadDetailsForItem = (id1,id2) ->
      $http.post($scope.settings.baseUrl + "/ng-item-details",
       id1:id1,
       id2:id2
      ).then (response) ->
        if(response.data)
          return response.data
        else
          return $q.reject('cannot load details for object');
        # item.data.safedescription = $sce.trustAsHtml(item.data.description)
        # $scope.updateQueryFromUrl(); #trigger opening of showtime-popup




  ]
