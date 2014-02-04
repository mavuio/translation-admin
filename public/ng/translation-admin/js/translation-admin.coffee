app = angular.module "translation-admin", ['ui.tinymce']

app.controller 'MainCtrl', ['$scope', '$location', '$http', '$filter', '$sce', '$timeout',
($scope, $location, $http, $filter, $sce, $timeout) ->
  $scope.app = {}
  $scope.query = {}
  $scope.settings = {} #for legacy stuff
  $scope.app.currentExpandedItem=null;

  $scope.updateUrl = (args={}) ->
     q=$scope.getQueryParams();
     console.log "updateUrl" , args, q  if window.console and console.log

     if args.path?
       if not $scope.startsWith($location.path(),args.path)
         $location.path args.path
       if args.path is 'empty'
         $location.path('')
     $location.search q
     # $location.path '/213123/tickets'
     # $location.hash 'abc'

  $scope.$watch (->
    $location.url()
  ), (url) ->
    if url
      $scope.updateQueryFromUrl()

  $scope.getQueryParams = ->
    params={}
    for key, value of $scope.query
       params[key]=value unless key in ['daterange','datestr']
    params





  $scope.updateQueryFromUrl = ->
      console.log "updateQueryFromUrl" , $location.search()  if window.console and console.log
      newquery=$location.search()
      for key,val of newquery
        $scope.query[key] = (if (val is "null") then null else val)

      # if newquery.date1? || newquery.date2?
      #   $scope.query.daterange.startDate=new moment(newquery.date1)
      #   $scope.query.daterange.endDate=new moment(newquery.date2)

      currentQuerystring= JSON.stringify $scope.getQueryParams()
      if $scope.oldQueryString isnt currentQuerystring
        $scope.$broadcast 'update_listing'
      $scope.oldQueryString=currentQuerystring

      #check path
      pathparts=$location.path().split('/');
      # console.log "pp" , pathparts  if window.console and console.log

      itemid=pathparts[1] if pathparts.length

      # console.log "pathparts" , pathparts.length,pathparts, itemid  if window.console and console.log

      #expand if needed
      $timeout(
        ()->
          if not $scope.app.currentExpandedItem
            console.log 'expand_item', itemid  if window.console and console.log and  itemid
            $scope.$broadcast 'expand_item', itemid if itemid
        1000
       )



  $scope.startsWith = (str1,str2) ->
    ret=str1.slice(0, str2.length) == str2;
    # console.log " #{str1} startsWith  #{str2} =#{ret}",str1.slice(0, str2.length),str1   if window.console and console.log
    ret


  $scope.init = (settings) ->
    $scope.settings=settings
    console.log "inited" , $scope.settings  if window.console and console.log

]


app.config [ '$locationProvider', ($locationProvider) ->

   # Note: Setting html5Mode to true seems to cause problems in browsers that doesn't support it, even though it's supposed to just ignore it and use the default mode. So it might be a good idea to check for support before turning it on, for example by checking Modernizr.history.

          $locationProvider.html5Mode false
    ]

