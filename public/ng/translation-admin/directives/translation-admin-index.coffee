angular.module("translation-admin").directive "translationAdminIndex", ->
  restrict: "A"
  replace: true
  templateUrl: '/packages/werkzeugh/translation-admin/ng/translation-admin/partials/translation-admin-index.html'
  scope: true
  link: (scope, element, attrs) ->


  controller: ["$scope", "$element", "$attrs", "$timeout", "$filter", "$http", "$q", ($scope, $element, $attrs, $timeout, $filter, $http, $q) ->

    $scope.items={}
    $scope.ngBaseUrl=$attrs.ngBaseUrl
    $scope.listmode='loading';
    $scope.items=[]
    $scope.available_languages=[]

    $scope.query=
      'lang1':'de'
      'lang2':'en'




    $scope.getItemForId = (id)->
      filterFilter = $filter('filter')
      res=filterFilter($scope.items,{lang1_id:id})
      if res.length
        return res[0]
      else
        return null

    $scope.refreshListing = ->
      $scope.updateUrl()

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

    $scope.refreshListing()

  ]
