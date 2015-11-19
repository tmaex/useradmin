<?php
require_once('config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$groupOus = GroupOu::readGroupOus($ldapconn);
ldap_close($ldapconn);

define('USE_ANGULAR', true);

?>
<?php include('html_head.inc.php'); ?>

  <body>

<?php include('navigation.inc.php'); ?>

    <div class="container" ng-controller="AddUserController as adduser">
      <!-- show alerts -->
      <div id="alert-container" class="container">
        <div class="col-xs-3"></div>
        <div class="col-xs-6">
          <uib-alert ng-repeat="alert in adduser.alerts.alertList"
              type="{{alert.type}}"
              close="alert.close()"
              dismiss-on-timeout="{{alert.dismiss}}">
            {{alert.msg}}
          </uib-alert>
        </div>
        <div class="col-xs-3"></div>
      </div>

      <h1>User anlegen</h1>

      <div ng-show="adduser.step === 1">
        <form class="form-horizontal" role="form">
          <div class="form-group">
            <label class="control-label col-sm-2" for="pwd">Vorname:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="givenName" ng-model="adduser.user.givenName" />
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="pwd">Nachname:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="sn" ng-model="adduser.user.sn" />
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="email">Username:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="username" ng-model="adduser.user.cn" />
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="email">E-Mail:</label>
            <div class="col-sm-10">
              <input type="email" class="form-control" id="mail" ng-model="adduser.user.mail" />
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button class="btn btn-default" ng-click="adduser.completeStep1()">
                User anlegen
              </button>
            </div>
          </div>
        </form>
      </div>

      <!--<form>
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon"><i class="fa fa-search"></i></div>
            <input type="text" class="form-control"
                placeholder="Suchen" ng-model="list.searchText">
          </div>
        </div>
      </form>-->

      <table class="table table-hover sortable">
        <!-- Titelzeile der Tabelle mit Sortiermöglichkeiten -->
        <!--<tr>
          <th ng-click="list.sortClick('cn')">
            cn
            <span ng-show="list.sortField === 'cn'"
                class="fa fa-caret-down"
                ng-class="{'fa-caret-down': !list.sortReverse,
                  'fa-caret-up': list.sortReverse}">
            </span>
          </th>
          <th ng-click="list.sortClick('displayName')">
            Name
            <span ng-show="list.sortField === 'displayName'"
                class="fa fa-caret-down"
                ng-class="{'fa-caret-down': !list.sortReverse,
                  'fa-caret-up': list.sortReverse}">
            </span>
          </th>
          <th ng-click="list.sortClick('mail')">
            E-Mail
            <span ng-show="list.sortField === 'mail'"
                class="fa fa-caret-down"
                ng-class="{'fa-caret-down': !list.sortReverse,
                  'fa-caret-up': list.sortReverse}">
            </span>
          </th>
        </tr>-->

        <!-- Tabelleneintrag für Benutzer -->
        <!--<tr ng-repeat-start="user in list.userData
              | orderBy:list.sortType:list.sortReverse
              | filter:list.searchText"
            ng-if="!user.expanded"
            ng-click="list.expandClick(user)">
          <td>{{user.cn}}</td>
          <td>{{user.displayName}}</td>
          <td>{{user.mail}}</td>
        </tr>-->

        <!-- Details für Benutzer -->
        <!--<tr ng-repeat-end="" ng-if="user.expanded">
          <td colspan="3">
            <div class="well">
              <a href="#" class="close" aria-label="close"
                  ng-click="list.expandClick(user)">
                &times;
              </a>
              <div style="text-align: center" ng-if="user.loading">
                <span class="fa fa-refresh"
                    ng-class="{'fa-spin' : user.loading}"></span>
              </div>
              <table class="userdetails" ng-if="!user.loading">
                <tr>
                  <th>cn:</th>
                  <td>{{user.cn}}</td>
                </tr>
                <tr>
                  <th>Name:</th>
                  <td>
                    <usradm-edit-text usradm-field="user.displayName"
                        onbeforesave="list.updateDisplayName(data, form, user)">
                    </usradm-edit-text>
                  </td>
                </tr>
                <tr>
                  <th>E-Mail:</th>
                  <td>
                    <usradm-edit-text usradm-field="user.mail"
                        onbeforesave="list.updateMail(data, form, user)">
                    </usradm-edit-text>
                  </td>
                </tr>
                <tr>
                  <th class="lblGruppen">Gruppen:</th>
                  <td>
                    <ul ng-if="user.details.groups.length">
                      <li ng-repeat="group in user.details.groups">
                        {{group.cn}}
                        <span class="small">({{group.description}})</span>
                        <span class="fa fa-refresh"
                            ng-show="list.groupIsRemoving(user, group)"
                            ng-class="{'fa-spin' :
                                list.groupIsRemoving(user, group)}"></span>
                        <span class="glyphicon glyphicon-minus clickable"
                            ng-click="list.removeGroupFromUser(user, group)">
                        </span>
                      </li>
                    </ul>
                        <span class="fa fa-refresh"
                            ng-show="list.groupIsAdding(user)"
                            ng-class="{'fa-spin' :
                                list.groupIsAdding(user)}"></span>
                    <span class="glyphicon glyphicon-plus clickable"
                        ng-click="list.addGroup(user)">
                    </span>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
      </table>-->

      <!-- Modal-Dialog zur Gruppenauswahl zum Hinzufügen -->
      <div id="groupAddModal" class="modal fade" role="dialog">
          <div class="modal-dialog">

          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close"
                  data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Gruppe hinzufügen</h4>
            </div>
            <div class="modal-body">
              <div id="accordion" class="panel-group">
                <div class="panel panel-default" ng-repeat="ou in adduser.groupData">
                  <div data-toggle="collapse" href="#collapse{{ou.ou}}"
                      data-parent="#accordion"
                      class="panel-heading clickable">
                    <h4 class="panel-title">
                      {{ou.ou}}
                      <span class="small">
                        ({{ou.dn}})
                      </span>
                    </h4>
                    <p class="list-group-item-text">
                      {{ou.description}}
                    </p>
                  </div>
                  <div id="collapse{{ou.ou}}" class="panel-collapse collapse">
                    <ul class="list-group" ng-if="ou.groups.length">
                      <li class="list-group-item clickable"
                          ng-repeat="group in ou.groups"
                          ng-show="!list.addGroupUserHasGroup(group)"
                          ng-click="list.addGroupToUser(group)">
                        <h5 class="list-group-item-heading">
                          {{group.cn}}
                          <span class="small">
                            ({{group.dn}})
                          </span>
                        </h5>
                        <p class="list-group-item-text">
                          {{group.description}}
                        </p>
                      </li>
                    </ul>
                    <div class="panel-body" ng-if="!ou.groups.length">
                      Keine Gruppen in dieser Kategorie.
                    </div>
                  </div> <!-- panel-collapse -->
                </div> <!-- panel -->
              </div> <!-- panel-group -->
            </div> <!-- modal-body -->
            <div class="modal-footer">
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- data for the group list (is then parsed by AngularJS) -->
    <script type="application/json" json-data id="jsonGroups">
      <?php echo json_encode($groupOus, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>
    </script>

<?php include('html_bottom.inc.php'); ?>