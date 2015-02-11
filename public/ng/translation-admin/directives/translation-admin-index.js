angular.module("translation-admin").directive("translationAdminIndex", function() {
  return {
    restrict: "A",
    replace: true,
    templateUrl: '/packages/werkzeugh/translation-admin/ng/translation-admin/partials/translation-admin-index.html',
    scope: true,
    link: function(scope, element, attrs) {},
    controller: [
      "$scope", "$element", "$attrs", "$timeout", "$filter", "$http", "$q", function($scope, $element, $attrs, $timeout, $filter, $http, $q) {
        $scope.ngBaseUrl = $attrs.ngBaseUrl;
        $scope.available_languages = [];
        $scope.groups = [];
        $scope.listmode = 'loading';
        $scope.items = [];
        $scope.allItems = [];
        $scope.app.currentExpandedItem = null;
        $scope.query.lang1 = 'de';
        $scope.query.lang2 = 'en';
        if ($scope.settings.query) {
          $scope.query = angular.extend($scope.query, $scope.settings.query);
        }
        $scope.getItemForId = function(id) {
          var filterFilter, res;
          filterFilter = $filter('filter');
          res = filterFilter($scope.items, {
            lang1_id: id
          });
          if (res.length) {
            return res[0];
          } else {
            return null;
          }
        };
        $scope.fetchGroups = function() {
          return $http.post($scope.settings.baseUrl + "/ng-groups", {
            query: $scope.query
          }).then(function(response) {
            return $scope.groups = response.data.items;
          });
        };
        $scope.refreshListing = function(page) {
          var mypath;
          if (page == null) {
            page = 1;
          }
          $scope.query.page = page;
          mypath = 'empty';
          if ($scope.query.category) {
            mypath = '/' + $scope.query.category;
          }
          if (window.console && console.log) {
            console.log("refreshListing", $scope.query);
          }
          $scope.updateUrl({
            path: mypath
          });
          return $http.post($scope.settings.baseUrl + "/ng-items", {
            query: $scope.query
          }).then(function(response) {
            $scope.items = response.data.items;
            $scope.available_languages = response.data.available_languages;
            if (window.console && console.log) {
              console.log("items loaded", $scope.items);
            }
            $scope.listmode = "loaded";
            if ($scope.items.length === 0) {
              $scope.listmode = "empty";
            }
            return $scope.updateQueryFromUrl();
          });
        };
        $scope.updateQueryFromUrl();
        $scope.$on('update_listing', function(event) {
          if (window.console && console.log) {
            console.log("received uodate listing", null);
          }
          return $scope.refreshListing();
        });
        $scope.fetchGroups();
        return $scope.refreshListing();
      }
    ]
  };
});

angular.module("translation-admin").filter("underscore_breaks", function($sce) {
  return function(input) {
    return $sce.trustAsHtml(input.replace(/_/g, '<wbr/>_'));
  };
});
