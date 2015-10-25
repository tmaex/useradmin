(function(){
  var userlistApp = angular.module('userlistApp',
      ['ngAnimate', 'xeditable', 'ui.bootstrap']);

  userlistApp.run(function(editableOptions) {
    editableOptions.theme = 'bs3';
  });

  userlistApp.controller('ListController', function($http) {
    this.sortField = 'cn';
    this.sortReverse = false;
    this.searchText = '';

    this.userAddGroup = false;

    this.groupRemoving = {};

    this.userData = JSON.parse(document.getElementById('jsonUsers').textContent);
    this.groupData = JSON.parse(document.getElementById('jsonGroups').textContent);

    this.alerts = [];
    this.closeAlert = function(index) {
      this.alerts.splice(index, 1);
    };

    for (var i = 0; i < this.userData.length; i++) {
      this.userData[i].userId = i;
      this.userData[i].expanded = false;
      this.userData[i].details = null;
      this.userData[i].detailsLoaded = false;
      this.userData[i].loading = false;
      this.groupRemoving[this.userData[i].dn] = {};
    }

    this.sortClick = function(field) {
      if (this.sortField === field) {
        this.sortReverse = !this.sortReverse;
      }
      this.sortField = field;
    };

    this.expandClick = function(userId) {
      this.userData[userId].expanded = !this.userData[userId].expanded;
      if (!this.userData[userId].detailsLoaded) {
        this.loadDetail(userId);
      }
    };

    this.loadDetail = function(userId) {
      var that = this;
      this.userData[userId].loading = true;
      $http.get('getUserDetails.json.php',
          {params: {dn: this.userData[userId].dn}})
          .success(function(data) {
        that.userData[userId].details = data;
        that.userData[userId].detailsLoaded = true;
        that.userData[userId].loading = false;
        that.userData[userId].groupDns = {};
        that.userData[userId].details.groups.map(function(item) {
          that.userData[userId].groupDns[item.dn] = item;
        });
      });
    };

    this.formatJson = function(json_str) {
      return JSON.stringify(json_str, undefined, 2);
    };

    this.updateMail = function(data, form, user) {
      form.loading = true;
      form.success = false;
      form.fail = false;
      $http.post('changeUserDetail.php',
          {'dn': user.dn,
            'newMail': data})
          .then(function(response) {
            // success
            form.loading = false;
            form.success = true;
            if (typeof response.data.mail != 'undefined') {
              user.mail = response.data.mail;
            }
          }, function(response) {
            // error
            form.loading = false;
            form.fail = true;
            if (typeof response.data.mail != 'undefined') {
              user.mail = response.data.mail;
            }
          });
      return false;
    };

    this.resetEditableForm = function(form) {
      form.loading = false;
      form.success = false;
      form.fail = false;
    };

    this.addGroup = function(user) {
      this.userAddGroup = user;
      angular.element('#groupAddModal').modal('show');
    };

    this.addGroupUserHasGroup = function(group) {
      if (!this.userAddGroup) {
        return false;
      }
      return this.userAddGroup.groupDns.hasOwnProperty(group.dn);
    };

    this.addGroupToUser = function(group) {
      var user = this.userAddGroup;
      var that = this;
      user.loading = true;
      angular.element('#groupAddModal').modal('hide');
      $http.post('addUserGroup.json.php',
          {'userdn': this.userAddGroup.dn,
            'groupdn': group.dn})
          .then(function(response) {
            // success
            console.log("success");
            console.log(response);
            that.loadDetail(user.userId); // resets userAddGroup.loading
            that.alerts.push(
              {type: 'success',
                msg: 'Benutzer ' + user.cn + ' zu Gruppe '
                    + group.cn + ' hinzugefügt',
              dismiss: 5000});
          }, function(response) {
            // error
            console.log("error");
            console.log(response);
            that.loadDetail(user.userId); // resets userAddGroup.loading
            that.alerts.push(
              {type: 'danger',
                msg: 'Konnte Benutzer ' + user.cn + ' nicht zu Gruppe '
                    + group.cn + ' hinzufügen'});
          });
    };

    this.removeGroupFromUser = function(user, group) {
      var that = this;
      this.groupRemoving[user.dn][group.dn] = true;
      $http.post('removeUserGroup.json.php',
          {'userdn': user.dn,
            'groupdn': group.dn})
          .then(function(response) {
            // success
            console.log("success");
            console.log(response);
            user.details.groups.splice(user.details.groups.indexOf(group), 1)
            delete user.groupDns[group.dn];
            that.groupRemoving[user.dn][group.dn] = false;
            that.alerts.push(
              {type: 'success',
                msg: 'Benutzer ' + user.cn + ' aus Gruppe '
                    + group.cn + ' entfernt',
              dismiss: 5000});
          }, function(response) {
            // error
            console.log("error");
            console.log(response);
            that.alerts.push(
              {type: 'danger',
                msg: 'Konnte Benutzer ' + user.cn + ' nicht aus Gruppe '
                    + group.cn + ' entfernen'});
          });
    };

    this.groupIsRemoving = function(user, group) {
      return this.groupRemoving[user.dn][group.dn];
    }
  });

})();
