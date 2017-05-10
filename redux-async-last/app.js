import React from 'react';
import ReactDOM from 'react-dom';
import { createStore, applyMiddleware } from 'redux';
import Counter from './component';
import reducer from './reducers';

// const customMiddleware = store => next => action => {
  
//   // console.log("middleware triggered", action);
//   if (action.type === "START_REQUEST") {
    
//     let newAction = Object.assign(action, {requestId: customRequestId });
//     next(newAction);
//   } else {
//     next(action);
//   }
// };

//const store = createStore(reducer, applyMiddleware(customMiddleware));

const store = createStore(reducer);

function delayedAction(type) {
  const requestId = new Date().getTime();
  store.dispatch({ type: "START_REQUEST", requestId: requestId});
  setTimeout(() => store.dispatch({ type: type, requestId: requestId }), 1000);
}

const render = () => ReactDOM.render( // turns ReactDOM.render into executable function
  <Counter
    value={store.getState().count}
    onIncrement={() => delayedAction('INCREMENT')}
    onDecrement={() => delayedAction('DECREMENT')}
  />,
  document.getElementById('root')
);

render();
store.subscribe(render);
