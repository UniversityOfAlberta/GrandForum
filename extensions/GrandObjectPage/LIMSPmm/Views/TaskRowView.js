var TaskRowView = Backbone.View.extend({
    tagName: 'tr',

    template: _.template($('#task_row_template').html()),

    editDialog: null,

    initialize: function(options) {
        this.project = options.project;
        this.isEditMode = options.isEditMode;
        
        this.listenTo(this.model, "sync", this.render);
        
        if (this.isEditMode && this.model.startTracking) {
            this.model.startTracking(); 
        }
    },

    events: {
        "click #deleteTask": "deleteTask",
        "click #checkStatus": "checkStatus",
        "click .download-merged-csvs": "downloadMergedCsvs"
    },

    saveTask: function() {
        if (!this.isEditMode) return null;

        if (this.model.unsavedAttributes() !== false) {
            return this.model.save(null, {
                success: function() {
                    console.log("Task '" + this.model.get('task') + "' saved successfully!");
                    this.$el.css('background-color', '#d4edda').animate({backgroundColor: 'transparent'}, 1000);
                }.bind(this)
            });
        }
        return null;
    },

    render: function() {
        if (this.isEditMode) {
            this.prepareDisplayState();
        } else {
            this.updateTaskSummary();
        }
        
        var templateData = this.model.toJSON();
        templateData.project = this.project.toJSON();
        templateData.isEditMode = this.isEditMode;

        this.$el.html(this.template(templateData));

        if (this.isEditMode) {
            this.renderTinyMCE();
            _.defer(function() {
                this.$('select[name=assignees]').chosen();
            }.bind(this));
        }

        return this;
    },
    
    deleteTask: function() {
        if (confirm('Are you sure you want to delete this task?')) {
            this.model.toDelete = true;
            this.model.trigger("change:toDelete");
            this.remove();
        }
    },
    
    checkStatus: function(){
        var view = new LIMSStatusCheckViewPmm({el: this.editDialog, model: this.model, isDialog: true, project: this.project});
        
        this.editDialog.view = view;
        $('body').append(this.editDialog);

        if (this.editDialog.dialog('instance')) {
            this.editDialog.dialog('destroy');
        }
        
        $('body').append(this.editDialog);
        
        this.editDialog.dialog({
            height: $(window).height() * 0.75,
            width: 600,
            title: "Check Task Status"
        });

        this.editDialog.dialog('open');
    },

    isRowVisible: function() {
        var isAssignee = this.model.get('isCurrentUserAssignee');
        var isReviewer = this.model.get('isCurrentUserReviewer');
        var isLeader = this.model.get('isLeaderAllowedToEdit');

        return isAssignee || isReviewer || isLeader;
    },

    updateTaskSummary: function() {
        var primaryData = this.model.toJSON();
        var currentUserId = me.get('id');
        var originalAssignees = primaryData.assignees || [];
        
        var isEveryoneAssigned = originalAssignees.some(function(a) { return a.id === -1; });
        var effectiveAssignees;
        if (isEveryoneAssigned && this.project && this.project.members) {
            effectiveAssignees = this.project.members.toJSON();
        } else {
            effectiveAssignees = originalAssignees;
        }
        var statuses = primaryData.statuses || {};
        var effectiveStatusValues = effectiveAssignees.map(function(assignee) {
            return statuses[assignee.id] || 'Assigned';
        });
        var closedCount = effectiveStatusValues.filter(function(s) { return s === 'Closed'; }).length;
        var pendingReviewCount = effectiveStatusValues.filter(function(s) { return s === 'Done'; }).length;
        var accountedFor = closedCount + pendingReviewCount;
        var assignedCount = effectiveAssignees.length - accountedFor;

        var reviewersObject = primaryData.reviewers || {}; 
        var isCurrentUserAssignee = originalAssignees.some(function(assignee) {
            return assignee.id === currentUserId || assignee.id === -1;
        });

        var reviewerList = Object.values(reviewersObject);
        var isCurrentUserReviewer = reviewerList.some(function(reviewer) {
            return reviewer && reviewer.id === currentUserId;
        });

        this.model.set({
            displayClosedCount: closedCount,
            displayPendingReviewCount: pendingReviewCount,
            displayAssignedCount: assignedCount,
            isCurrentUserAssignee: isCurrentUserAssignee,
            isCurrentUserReviewer: isCurrentUserReviewer
        });
    },
    
    prepareDisplayState: function() {
        var primaryData = this.model.toJSON();
        var isEveryoneAssigned = _.some(primaryData.assignees, function(a) { return (a.id || a) == -1; });
        var displayAssignees = isEveryoneAssigned ? this.project.members.toJSON() : primaryData.assignees;

        var displayStatuses = {}, displayFiles = {}, displayReviewers = {}, displayComments = {};

        displayAssignees.forEach(function(assignee) {
            var assigneeId = assignee.id.toString();
            displayStatuses[assigneeId]  = primaryData.statuses[assigneeId]  || 'Assigned';
            displayFiles[assigneeId]     = _.clone(primaryData.files[assigneeId]) || {};
            displayReviewers[assigneeId] = primaryData.reviewers[assigneeId] || {};
            displayComments[assigneeId] = primaryData.comments[assigneeId] || '';
        }, this);

        var statusValues = Object.values(displayStatuses);
        var closedCount = statusValues.filter(function(s) { return s === 'Closed'; }).length;
        var pendingReviewCount = statusValues.filter(function(s) { return s === 'Done'; }).length;
        var accountedFor = closedCount + pendingReviewCount;
        var assignedCount = displayAssignees.length - accountedFor;

        var currentUserId = me.get('id');
        var originalAssignees = primaryData.assignees || [];
        var isCurrentUserAssignee = originalAssignees.some(function(assignee) {
            return (assignee.id || assignee) === currentUserId || (assignee.id || assignee) === -1;
        });
        var reviewerList = Object.values(displayReviewers);
        var isCurrentUserReviewer = reviewerList.some(function(reviewer) {
            return reviewer && reviewer.id === currentUserId;
        });

        this.model.set({
            displayAssignees: displayAssignees,
            displayStatuses: displayStatuses,
            displayFiles: displayFiles,
            displayReviewers: displayReviewers,
            displayComments: displayComments,
            displayClosedCount: closedCount,
            displayPendingReviewCount: pendingReviewCount,
            displayAssignedCount: assignedCount,
            isCurrentUserAssignee: isCurrentUserAssignee,
            isCurrentUserReviewer: isCurrentUserReviewer,
            isEveryoneAssigned: isEveryoneAssigned,
        });

    },

    downloadMergedCsvs: function(e) {
        e.preventDefault();

        if (!this.model || !this.model.id) {
            alert('Error: Task ID is not available.');
            return;
        }
        
        var taskId = this.model.id;
        var restPath = 'api.limstaskpmm/' + taskId + '/merge_csvs';
    
        var apiUrl = wgServer 
               + wgScriptPath 
               + '/index.php?action=' + restPath;

        $.ajax({
            url: apiUrl,
            method: 'GET',
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data, textStatus, xhr) {
                var contentType = xhr.getResponseHeader('content-type');
                if (contentType && contentType.includes('text/csv')) {
                    var blob = new Blob([data], { type: 'text/csv' });
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = xhr.getResponseHeader('content-disposition')
                        .split('filename=')[1].replace(/"/g, '') || 'merged_data.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                var errorMessage = 'An error occurred while downloading CSV files.';
                if (xhr.status === 404) {
                    errorMessage = 'No CSV files found for this task.';
                } else if (xhr.status === 400) {
                    errorMessage = 'Invalid task ID provided.';
                }
                alert( errorMessage);
            }.bind(this)
        });
    },

    renderTinyMCE: function(){
        _.defer(function(){
            this.$('textarea').tinymce({
                theme: 'modern',
                menubar: false,
                statusbar: false,
                relative_urls : false,
                convert_urls: false,
                plugins: 'link image charmap lists table paste',
                toolbar: [
                    'bold | link | bullist numlist'
                ],
                default_link_target: "_blank",
                rel_list: [
                    {title: 'No Referrer No Opener', value: 'noreferrer noopener'}
                ],
                paste_data_images: true,
                invalid_elements: 'h1, h2, h3, h4, h5, h6, h7, font',
                imagemanager_insert_template : '<img src="{$url}" width="{$custom.width}" height="{$custom.height}" />',
                setup: function(editor){
                    editor.on('change', function(e){
                        this.model.set('details',editor.getContent());
                    }.bind(this));
                }.bind(this)

            });
        }.bind(this));
    },
    
    handleAssigneeChange: function(model, allAssignees) {
        var fullAssigneeObjects = _.map(allAssignees, function(item) {
            var assigneeId;
            if (_.isObject(item)) {
                if (item.name) {
                    return item;
                }
                assigneeId = item.id;
            } else {
                assigneeId = item;
            }
            if (assigneeId == -1) {
                return { id: -1, name: "Everyone", url: "" };
            }
            var userObject = this.project.members.get(assigneeId);
            return userObject ? {
                id: userObject.get('id'),
                name: userObject.get('fullName'),
                url: userObject.get('url') || ''
            } : { id: assigneeId, name: "Unknown", url: "" };
        }, this);
        this.model.set('assignees', fullAssigneeObjects, {silent: true});

        // Set 'Assigned' as a default status
        var currentStatuses = _.clone(this.model.get('statuses')) || {};

        fullAssigneeObjects.forEach(function(assignee) {
            var assigneeId = assignee.id.toString();
            if (!currentStatuses[assigneeId]) {
                currentStatuses[assigneeId] = 'Assigned';
            }
        });
        this.model.set('statuses', currentStatuses);

        var currentReviewers = _.clone(this.model.get('reviewers')) || {};
        var allMembers = this.project.members.toJSON();
        var updatedReviewers = LIMSPmmHelper.assignReviewersToNewUsers(allAssignees, currentReviewers, allMembers);
        this.model.set('reviewers', updatedReviewers);
        this.prepareDisplayState();
        this.updateCounts();
        this.updateAssigneeOptions();
    },

    updateCounts: function() {
        this.$('.count-completed').text(this.model.get('displayClosedCount'));
        this.$('.count-pending').text(this.model.get('displayPendingReviewCount'));
        this.$('.count-assigned').text(this.model.get('displayAssignedCount'));
    },

    updateAssigneeOptions: function(){
        var assigneesSelect = this.$('select[name=assignees]');
        var selectedAssignees = assigneesSelect.val() || [];
        if (selectedAssignees.includes('-1')) {
            assigneesSelect.find('option').not('[value="-1"]').prop('disabled', true);
            
            setTimeout(function() {
                var chosenContainer = assigneesSelect.next('.chosen-container');
                chosenContainer.find('.chosen-choices .search-choice').each(function() {
                    var choiceText = $(this).find('span').text().trim();
                    if (choiceText !== 'Everyone') {
                        $(this).hide();
                    }
                });
            }, 50);
        } else {
            assigneesSelect.find('option').prop('disabled', false);
            var chosenContainer = assigneesSelect.next('.chosen-container');
            chosenContainer.find('.chosen-choices .search-choice').show();
        }
        assigneesSelect.trigger("chosen:updated");

        assigneesSelect.hide();
    },

    changeStatus: function(){
        // Create a model for the status change dialog
        var view = new LIMSStatusChangeViewPmm({el: this.editDialog, model: this.model, isDialog: true, project: this.project});

        this.editDialog.view = view;
        $('body').append(this.editDialog);

        // Check if the dialog is already initialized
        if (this.editDialog.dialog('instance')) {
            this.editDialog.dialog('destroy');
        }
        
        $('body').append(this.editDialog);
        
        this.editDialog.dialog({
            height: $(window).height() * 0.75,
            width: 900,
            title: "Change Task Status",
            close: function(){
                if (view.closeDialog) {
                    view.closeDialog();
                }
            }
        });

        // Open the dialog
        this.editDialog.dialog('open');
    },
    
});