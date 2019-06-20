const FlashNotifications = (($) => {
  const onReady = () => {
    listenIncomingMessages();
  }

  const listenIncomingMessages = () => {
    $(document).ajaxComplete((event, xhr, settings) => {
      const data = xhr.responseJSON;

      if (!data.messages) {
        return;
      }
      const messages = data.messages;

      if (messages.error) {
        messages.error.forEach(message => show(message, 'error'));
      }

      if (messages.success) {
        messages.success.forEach(message => show(message, 'success'));
      }
    });
  };

  const show = (message, type) => {
    let stringTemplate = getStringTemplate();;
    stringTemplate = setType(stringTemplate, type);
    stringTemplate = setMessage(stringTemplate, message);
    append(stringTemplate);
  };

  const getStringTemplate = () => {
    const templateRef = $('#flash-notification-template');
    const stringTemplate = templateRef.html();
    return stringTemplate;
  }

  const setType = (stringTemplate, type) => {
    const regExp = new RegExp('\\$\\{type\\}');
    return stringTemplate.replace(regExp, type);
  }

  const setMessage = (stringTemplate, message) => {
    const regExp = new RegExp('\\$\\{message\\}');
    return stringTemplate.replace(regExp, message);
  }

  const append = (stringTemplate) => {
    const flashNotificationsContainerRef = $('#flash-notifications-container');
    flashNotificationsContainerRef.append(stringTemplate);
  }

  // Public methods
  return {
    onReady
  }
})($);