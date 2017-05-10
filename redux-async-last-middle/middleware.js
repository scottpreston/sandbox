let requestId = 0;

const customMiddleware = store => next => action => {

    // console.log("middleware triggered", action);
    if (action.type === "START_REQUEST") {
        requestId = action.requestId;
        next(action);    
    } else {
        if (requestId !== action.requestId) {
            console.log("still loading, requestid out of sync...");
        } else {
            next(action)
        }
    }
};

export default customMiddleware;