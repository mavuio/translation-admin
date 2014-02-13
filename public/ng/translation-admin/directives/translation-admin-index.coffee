angular.module("translation-admin").directive "translationAdminIndex", ->
  restrict: "A"
  replace: true
  templateUrl: '/packages/werkzeugh/translation-admin/ng/translation-admin/partials/translation-admin-index.html'
  scope: true
  link: (scope, element, attrs) ->


  controller: ["$scope", "$element", "$attrs", "$timeout", "$filter", "$http", "$q", ($scope, $element, $attrs, $timeout, $filter, $http, $q) ->

    $scope.ngBaseUrl=$attrs.ngBaseUrl
    $scope.available_languages=[]
    $scope.groups = []


    $scope.listmode='loading';
    $scope.items=[]
    $scope.allItems=[]
    $scope.app.currentExpandedItem=null;

    $scope.query.lang1='de'
    $scope.query.lang2='en'



    $scope.getItemForId = (id)->
      filterFilter = $filter('filter')
      res=filterFilter($scope.items,{lang1_id:id})
      if res.length
        return res[0]
      else
        return null


    $scope.fetchGroups = ->
      $http.post($scope.settings.baseUrl + "/ng-groups",
        query: $scope.query
      ).then (response) ->
        $scope.groups = response.data.items


    $scope.refreshListing = (page = 1) ->
      $scope.query.page=page
      mypath='empty'
      mypath='/'+$scope.query.category if $scope.query.category
      console.log "refreshListing" , $scope.query  if window.console and console.log

      $scope.updateUrl path:mypath

      $http.post($scope.settings.baseUrl + "/ng-items",
        query: $scope.query
      ).then (response) ->
        $scope.items = response.data.items
        $scope.available_languages = response.data.available_languages
        console.log "items loaded", $scope.items  if window.console and console.log
        $scope.listmode = "loaded"
        $scope.listmode = "empty"  if $scope.items.length is 0
        # $scope.toggleItem $scope.items[0]  if $scope.items.length is 1
        $scope.updateQueryFromUrl() #triggers loading of event-detail


    $scope.updateQueryFromUrl() # triggers loading

    $scope.$on 'update_listing', (event)->
      console.log "received uodate listing" , null  if window.console and console.log

      $scope.refreshListing()

    $scope.fetchGroups()
    $scope.refreshListing()

  ]

angular.module("translation-admin").filter "underscore_breaks", ($sce)->
  (input) ->
    $sce.trustAsHtml(input.replace /_/g , '<wbr/>_')
