const Todo = (($, appBasePath) => {
  const resourcePath = 'todo';
  const deleteActionsItemsSelector = 'a[data-action="delete"]';
  const completedActionsItemsSelector = 'a[data-action="completed"]';

  // Pagination
  let paginatorElementRef;
  let currentPage = 0;
  let pageSize = 5;

  const onReady = () => {
    listenActionsClick();
  };

  const listenActionsClick = () => {
    // Listen for [todo] deletetion
    $(document).on('click', deleteActionsItemsSelector, handleDelete);
    // Listen for [todo] completed change
    $(document).on('click', completedActionsItemsSelector, handlePut);
  };

  const handleDelete = (event) => {
    // Prevent hash navigation
    event.preventDefault();

    const todoId = event.target.getAttribute('data-todo-id');
    
    // If it doesn't has todoId defined just return
    if (!todoId) {
      return;
    }

    const callbackPath = event.target.getAttribute('data-callback-path');
    const todoDescription = event.target.getAttribute('data-todo-description');

    const modalMessage = `Do you want to delete '${todoDescription}'?`;
    const ajaxOptions = {
      method: 'delete',
      url: `${appBasePath}/${resourcePath}/${todoId}`
    };

    showModal(modalMessage)
      .then(() => ajaxRequestPromise(ajaxOptions))
      .then(() => {
        // If callback path configurated, it reload
        if (callbackPath) {
          window.location.href = `${appBasePath}/${callbackPath}`;
        } else {
          removeDomElementWithAnimation(todoId);
        }
      })
      .catch(() => null);
  };

  const handlePut = (event) => {
    // Prevent hash navigation
    event.preventDefault();

    const todoId = event.target.getAttribute('data-todo-id');
    const currentCompletedStatus = event.target.getAttribute('data-completed');

    // If it doesn't has todoId or completed status defined just return
    if (!todoId || !currentCompletedStatus) {
      return;
    }

    const callbackPath = event.target.getAttribute('data-callback-path');
    const ajaxOptions = {
      method: 'put',
      url: `${appBasePath}/${resourcePath}/${todoId}`,
      data: {
        completed: parseInt(currentCompletedStatus) ? 0 : 1
      }
    };

    ajaxRequestPromise(ajaxOptions)
      .then(() => {
        // If callback path configurated, it reload
        if (callbackPath) {
          window.location.href = `${appBasePath}/${callbackPath}`;
        } else {
          // Refresh view
          refreshView();
        }
      })
      .catch((e) => console.log('fail', e));
  }

  const showModal = (message) => {
    return new Promise((resolve, reject) => {
      // Modal ref
      const modalElem = $('#confirm-modal');
      // Default message
      const defaultMessage = 'Are you sure?';
  
      // Set modal body message
      modalElem.find('.modal-body').text(message || defaultMessage);
      // Show the modal
      modalElem.modal();
      // Listen modal buttons click
      modalElem.find('button').click((event) => {
        // Unbind the listener
        $(event.target).unbind(event);
        
        const modalResult = $(event.target).data('result');
        
        if (modalResult) {
          return reject();
        }

        return resolve();
      })
    });
  }

  const ajaxRequestPromise = (options) => {
    // Ajax Http Request
    return new Promise((resolve, reject) => {
      $.ajax(options)
        .fail((e) => reject(e))
        .done((data) => resolve(data));
    });
  };

  const removeDomElementWithAnimation = (todoId) => {
    // [todo] row ref
    const todoRow = $(`#todo-row-${todoId}`);
    // Fade out DOM element
    todoRow.fadeOut('slow', () => {
      // Remove DOM element
      todoRow.remove();
      
      // Refresh view
      refreshView(true);
    });
  }

  const parseTodos = (todos) => {
    const todoRowTemplateRef = $('#todo-row-template');
    const todoRowStringTemplate = todoRowTemplateRef.html();
    const todosContainerRef = $('#todos-container');

    // Remove all existing ones
    todosContainerRef.empty();

    todos.forEach((todo) => {
      let newTodoRow = todoRowStringTemplate;
      
      // Adding custom template values
      todo.detail_href = `${appBasePath}/${resourcePath}/${todo.id}`;
      todo.status_text = parseInt(todo.completed) ? 'completed' : 'pending';
      todo.mark_as_button_text = parseInt(todo.completed) ? 'pending' : 'completed';
      todo.status_label_class = parseInt(todo.completed) ? 'success' : 'default';

      // Match [todo] properties
      Object.keys(todo).forEach(key => {
        const regExp = new RegExp(`\\$\\{${key}\\}`, 'g');
        newTodoRow = newTodoRow.replace(regExp, todo[key]);
      })

      todosContainerRef.append(newTodoRow);
    });
  };

  const initPaginator = () => {
    paginatorElementRef = $('#pages-container');
    paginatorElementRef.pagination({
      dataSource: `${appBasePath}/${resourcePath}`,
      locator: 'data',
      pageNumber: currentPage,
      totalNumberLocator: (response) => response.total,
      pageSize: pageSize,
      className: 'paginationjs-theme-grey paginationjs-big',
      callback: function(todos, pagination) {
        $('.loading').addClass('hidden');
        
        if (!todos.length) {
          $('.no-items').removeClass('hidden');
          $('.items').addClass('hidden');
        } else {
          $('.no-items').addClass('hidden');
          $('.items').removeClass('hidden');
        }
        // Update currentPage value
        currentPage = pagination.pageNumber;

        // Parse data
        parseTodos(todos);
      }
    });
  };

  const refreshView = (check) => {
    $('.loading').removeClass('hidden');
    $('.no-items').addClass('hidden');
    $('.items').addClass('hidden');

    let nextPage = currentPage;
    if (check) {
      // Calculate the total of [todo] we have once we remove
      const totalTodosOnCurrentPage = paginatorElementRef.pagination('getSelectedPageData') .length- 1;
      // If we don't have more [todo] on current page and we have a previous one, move there
      if (!totalTodosOnCurrentPage && 1 < currentPage) {
        nextPage = currentPage - 1;
      } 
    }

    paginatorElementRef.pagination(nextPage);
  }

  // Public methods
  return {
    onReady,
    initPaginator
  }
})($, appBasePath);