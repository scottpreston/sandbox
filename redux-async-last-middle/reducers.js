export default (state = {count:0, requestId:0}, action) => {
  switch (action.type) {
    case 'START_REQUEST':{
      return {count: state.count, requestId: action.requestId};
    }
    case 'INCREMENT': {
          return {count: state.count + 1};
    }
    case 'DECREMENT':
          return {count: state.count - 1};
    default:
      return state;
  }
};

