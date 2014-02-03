angular.module("wien-import").directive "wienImportLocation", ->
  restrict: "A"
  replace: true
  templateUrl: "/mysite/ng/wien-import/partials/wien-import-location.html"
  scope: true
  link: (scope, element, attrs) ->

  controller: ["$scope", "$element", "$attrs", "$timeout", "$transclude", "$http", "$q", ($scope, $element, $attrs, $timeout, $transclude, $http, $q) ->

      $scope.subresult={}
      $scope.subquery={}

      $scope.$on 'expand_item', (event,id)->
        if +$scope.item.loc_id == +id  and not $scope.item?expanded
          $scope.toggleItem($scope.item)
          console.log "expand location on ", id,$scope.item if window.console and console.log

      $scope.tinymceOptions =
        menubar: false
        plugins: [ "code fullscreen contextmenu autoresize" ]
        toolbar1: "bold italic | undo redo | link unlink | removeformat | code fullscreen",
        autoresize_min_height:100
        autoresize_max_height:600


      $scope.associate_location = (item,l) ->
        $http.post($scope.settings.baseUrl + "ng-associate-location",
          foreign_loc:l
          live_id:item.loc_id
        ).then (response) ->
          console.log "associate-location" , response  if window.console and console.log
          if(response.data and response.data.status is 'ok')
             console.log "assoc" , response.data  if window.console and console.log

             item.foreign_locs=response.data.foreign_locs if response.data.foreign_locs
             return response
          else
             return $q.reject('cannot associate-location');


      $scope.de_associate_location = (item,l) ->
        $http.post($scope.settings.baseUrl + "ng-de-associate-location",
          foreign_loc:l
          live_id:item.loc_id
        ).then (response) ->
          console.log "de-associate-location" , response  if window.console and console.log
          if(response.data and response.data.status is 'ok')
             console.log "assoc" , response.data  if window.console and console.log

             item.foreign_locs=response.data.foreign_locs if response.data.foreign_locs
             return response
          else
             return $q.reject('cannot de-associate-location');


      $scope.toggleItem = (item, e) ->
        newval = (if item.expanded then false else true)
        if $scope.app.currentExpandedItem and $scope.app.currentExpandedItem isnt item
          $scope.app.currentExpandedItem.expanded = false
          $scope.app.currentExpandedItem = null

        if newval is true
          if typeof item.data is "undefined"
            item.data = {}
            item.record={loading:true}
            $scope.loadDetailsForItem(item.loc_id).then (itemdata) ->
              item.data=itemdata;

              if item.data.livedata
                item.record=angular.copy(item.data.livedata)

          $scope.app.currentExpandedItem = item
          $scope.updateUrl path:'/'+item.loc_id
          $timeout(
            ()->
              angular.element('.savebutton').focus()
            200
           )


        item.expanded = newval

        if $scope.app.currentExpandedItem and not $scope.app.currentExpandedItem.expanded
          $scope.updateUrl path:'empty'

      $scope.reload_subresult = ->
        console.log "reload subres" , null  if window.console and console.log
        $scope.subresult.status='loading'
        $http.post($scope.settings.baseUrl + "ng-get-importedlocations",
          query:$scope.subquery
        ).then (response) ->
          $scope.subresult=response.data

      $scope.skipItem = (item,e) ->
        if (confirm('diesen Ort wirklich aus der Liste der zu importierenden Locations entfernen ?'))
          $scope.setItemval('loc',item.loc_id,'Status','skipped').then ->
            $element.closest('li').slideUp 500, ()->
              this.remove()

      $scope.saveItem = (item,idx) ->
        $scope.saveDetailsForItem(item.record).then ->
          $element.closest('li').slideUp 500, ()->
            this.remove()
          $scope.toggleItem($scope.items[idx + 1],null)

      $scope.setItemval = (type,id,fieldname,value) ->
        $http.post($scope.settings.baseUrl + "ng-set-itemval",
          type:type
          id:id
          fieldname:fieldname
          value:value
        ).then (response) ->
          console.log "setItemval" , response  if window.console and console.log
          if(response.data and response.data.status is 'ok')
             return response
          else
             return $q.reject('cannot set value on object');


      $scope.loadDetailsForItem = (id) ->
        $http.post($scope.settings.baseUrl + "ng-location-details",
         id:id
        ).then (response) ->
          if(response.data)
            return response.data
          else
            return $q.reject('cannot load details for location');
          # item.data.safedescription = $sce.trustAsHtml(item.data.description)
          # $scope.updateQueryFromUrl(); #trigger opening of showtime-popup

      $scope.saveDetailsForItem = (record) ->
        $http.post($scope.settings.baseUrl + "ng-save-location",
         record:record
        ).then (response) ->
          if(response.data and response.data.status is 'ok')
             return response
          else
            return $q.reject('cannot save details for location');
          # item.data.safedescription = $sce.trustAsHtml(item.data.description)
          # $scope.updateQueryFromUrl(); #trigger opening of showtime-popup




    ]
