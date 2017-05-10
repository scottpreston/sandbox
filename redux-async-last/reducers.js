export default (state = {count:0, requestId:0}, action) => {
  switch (action.type) {
    case 'START_REQUEST':{
      return {count: state.count, requestId: action.requestId};
    }
    case 'INCREMENT': {
        if (state.requestId === action.requestId) {
          return {count: state.count + 1, requestId: 0};
        } else {
          console.log('request mismatch', action.requestId, state.requestId);
        }
    }
    case 'DECREMENT':
         if (state.requestId === action.requestId) {
          return {count: state.count - 1, requestId: 0};
        } else {
          console.log('request mismatch', action.requestId, state.requestId);
        }
    default:
      return state;
  }
};

