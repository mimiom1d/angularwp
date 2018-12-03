const gcwpApp = new angular.module("wpAngularTheme", [
  "ui.router",
  "ngResource"
]);

gcwpApp.factory("Posts", function($resource) {
  return $resource(appInfo.api_url + "posts/:ID", {
    ID: "@id"
  });
});
gcwpApp.factory("ATMs", function($resource) {
  return $resource(appInfo.api_url + "atm-location/:ID", {
    ID: "@id"
  });
});

gcwpApp.controller("ListCtrl", [
  "$scope",
  "Posts",
  function($scope, Posts) {
    console.log("ListCtrl");
    $scope.page_title = "Blog Listing Page";

    Posts.query(function(res) {
      $scope.posts = res;
    });
  }
]);

gcwpApp.controller("DetailCtrl", [
  "$scope",
  "$stateParams",
  "Posts",
  function($scope, $stateParams, Posts) {
    console.log($stateParams);
    Posts.get({ ID: $stateParams.id }, function(res) {
      $scope.post = res;
    });
  }
]);

gcwpApp.controller("ATMCtrl", [
  "$scope",
  "ATMs",
  function($scope, ATMs) {
    console.log("ATMCtrl");
    $scope.page_title = "ATM Page";

    ATMs.query(function(res) {
      console.log(res);
      $scope.atms = res;
    });
    // ATMs.get({ ID: $stateParams.id }, function(res) {
    //   $scope.post = res;
    // });
  }
]);
gcwpApp.controller("ATMDetailCtrl", [
  "$scope",
  "$stateParams",
  "ATMs",
  function($scope, $stateParams, ATMs) {
    console.log($stateParams);
    ATMs.get({ ID: $stateParams.id }, function(res) {
      $scope.post = res;
    });
  }
]);

gcwpApp.config(function($stateProvider, $urlRouterProvider) {
  // $urlRouterProvider.otherwise("/");
  $stateProvider
    .state("atm", {
      url: "/atm-location/",
      controller: "ATMCtrl",
      templateUrl: appInfo.template_directory + "templates/atm.html"
    })
    .state("atmdetail", {
      url: "/atm-location/:id",
      controller: "ATMDetailCtrl",
      templateUrl: appInfo.template_directory + "templates/atm-detail.html"
    })
    .state("list", {
      url: "/",
      controller: "ListCtrl",
      templateUrl: appInfo.template_directory + "templates/list.html"
    })
    .state("detail", {
      url: "/posts/:id",
      controller: "DetailCtrl",
      templateUrl: appInfo.template_directory + "templates/detail.html"
    });
});

// ** Create our own filter to use trustAsHtml method
// ** (Another way to do this is via controller: https://stackoverflow.com/questions/18340872/how-do-you-use-sce-trustashtmlstring-to-replicate-ng-bind-html-unsafe-in-angu)
gcwpApp.filter("to_trusted", [
  "$sce",
  function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]);
