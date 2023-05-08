'use strict';
/// <reference path="../../../js/jquery.d.ts" />
var AmeAcCommunicator;
(function (AmeAcCommunicator) {
    const $ = jQuery;
    const MessageFormatName = 'AmeAcCommunicator1';
    function connectToChild(frame, rpcMethods = {}, allowedOrigins = ['*']) {
        var _a;
        //Note: We could try to get the frame's origin from the src attribute,
        //but that would not work consistently because the user could navigate
        //to a different domain in the frame and the attribute would stay the same.
        return new Connection('Parent->Child', (_a = frame.contentWindow) !== null && _a !== void 0 ? _a : null, rpcMethods, allowedOrigins);
    }
    AmeAcCommunicator.connectToChild = connectToChild;
    function connectToParent(rpcMethods = {}, allowedOrigins = ['*']) {
        var _a;
        return new Connection('Child->Parent', (_a = window.parent) !== null && _a !== void 0 ? _a : null, rpcMethods, allowedOrigins);
    }
    AmeAcCommunicator.connectToParent = connectToParent;
    class Connection {
        constructor(name, target, rpcMethods = {}, allowedTargetOrigins = ['*'], connectionTimeout = 30000, myWindow = window) {
            this.name = name;
            this.target = target;
            this.rpcMethods = rpcMethods;
            this.allowedTargetOrigins = allowedTargetOrigins;
            this.myWindow = myWindow;
            this.handshakeDone = false;
            this.connectedOrigin = null;
            this.rpcRequestCount = 0;
            this.pendingRpcRequests = {};
            this.rpcTimeout = 30000;
            this.isConnectionBroken = false;
            this.log('Initializing...');
            this.deferred = $.Deferred();
            this.promise = this.deferred.promise();
            if (this.target === null) {
                this.log('Invalid target window.');
                this.deferred.reject();
                return;
            }
            //Try to auto-detect the origin. This won't work if the target is
            //loaded from a different domain.
            if (this.connectedOrigin === null) {
                try {
                    this.connectedOrigin = this.target.location.origin;
                    this.log(`Detected target origin: ${this.connectedOrigin}`);
                }
                catch (e) {
                    //Leave the origin as null.
                }
            }
            this.rpcIdPrefix = 'aAcC_' + Connection.getRandomString(8) + '_';
            this.messageListener = (event) => {
                if (event.source !== this.target) {
                    this.log('Ignoring message from unknown source.');
                    return;
                }
                if (!this.isAllowedOrigin(event.origin)) {
                    this.log('Ignoring message from disallowed origin: ' + event.origin);
                    return;
                }
                const message = parseMessage(event.data);
                if (message === null) {
                    this.log('Ignoring invalid message.');
                    return;
                }
                this.handleMessage(message, event.origin);
            };
            this.myWindow.addEventListener('message', this.messageListener);
            this.log('Event listener added.');
            //Send the handshake request to every allowed target origin.
            for (const origin of this.allowedTargetOrigins) {
                this.log(`Sending handshake request to ${origin}`);
                this.target.postMessage(this.createHandshakeRequest(), origin);
            }
            //Give up if the other window doesn't respond in time.
            if (connectionTimeout > 0) {
                setTimeout(() => {
                    if (this.deferred.state() === 'pending') {
                        this.deferred.reject();
                    }
                }, connectionTimeout);
            }
            //Clean up event listener(s) if the connection fails for any reason.
            this.deferred.fail(() => {
                this.myWindow.removeEventListener('message', this.messageListener);
            });
        }
        handleMessage(message, origin) {
            if (this.isConnectionBroken) {
                this.log('Ignoring message because the connection is broken.');
                return;
            }
            this.log(`Received message from ${origin}: ${JSON.stringify(message)}`);
            //Until the handshake is completed, process only handshake messages.
            if (!this.handshakeDone) {
                //We'll send a handshake request to the other window and wait
                //for a response. Alternatively, the other window can send
                //a handshake request to us.
                if (isHandshakeResponse(message)) {
                    if (this.validateHandshakeResponse(message)) {
                        this.completeHandshake(message, origin);
                    }
                    else {
                        this.deferred.reject();
                    }
                }
                else if (isHandshakeRequest(message)) {
                    if (this.validateHandshakeRequest(message)) {
                        this.send(this.createHandshakeResponse());
                        this.completeHandshake(message, origin);
                    }
                }
                return;
            }
            if (isRpcRequest(message)) {
                this.handleRpcRequest(message);
            }
            else if (isRpcResponse(message)) {
                this.handleRpcResponse(message);
            }
        }
        execute(method, ...args) {
            if (this.isConnectionBroken) {
                return $.Deferred().reject('Connection has already been closed.').promise();
            }
            this.rpcRequestCount++;
            const requestId = this.rpcIdPrefix + this.rpcRequestCount + '_' + Connection.getRandomString(6);
            const requestDef = $.Deferred();
            //Remember the request so that we can resolve it when we get the response.
            this.pendingRpcRequests[requestId] = requestDef;
            //We'll set a timeout later.
            let timeoutTimerId = null;
            //Always clean up the request collection and the timer when the request
            //is resolved, whether it was successful or not.
            requestDef.always(() => {
                delete this.pendingRpcRequests[requestId];
                if (timeoutTimerId !== null) {
                    clearTimeout(timeoutTimerId);
                }
            });
            const request = this.createMessage('rpc-request', {
                'method': method,
                'args': args,
                'requestId': requestId
            });
            this.deferred
                .done(() => {
                //Time out the request eventually.
                if (this.rpcTimeout > 0) {
                    timeoutTimerId = setTimeout(() => {
                        timeoutTimerId = null;
                        if (requestDef.state() === 'pending') {
                            requestDef.reject('RPC request timed out.');
                        }
                    }, this.rpcTimeout);
                }
                this.send(request);
            })
                .fail(() => {
                requestDef.reject('Connection failed.');
            });
            return requestDef.promise();
        }
        send(message) {
            if (this.isConnectionBroken) {
                return;
            }
            let origin = this.connectedOrigin;
            if (origin === null) {
                origin = '*';
            }
            this.target.postMessage(message, origin);
        }
        /**
         * Break the connection, e.g. when the iframe is removed from the page.
         */
        disconnect() {
            //Do nothing if already disconnected.
            if (this.isConnectionBroken) {
                return;
            }
            this.isConnectionBroken = true;
            try {
                this.myWindow.removeEventListener('message', this.messageListener);
            }
            catch (e) {
                //Do nothing if the window is not valid.
            }
            if (this.deferred.state() === 'pending') {
                this.deferred.reject();
            }
            //Reject all pending RPC requests.
            //The list could change during the loop, so we'll use a copy.
            const pendingRequests = Object.keys(this.pendingRpcRequests).map(key => this.pendingRpcRequests[key]);
            pendingRequests.forEach(request => {
                if (request.state() === 'pending') {
                    request.reject('Connection explicitly closed.');
                }
            });
        }
        createHandshakeRequest() {
            return this.createMessage('handshake-request', {
                'myOrigin': this.getMyOrigin(),
            });
        }
        createHandshakeResponse() {
            return this.createMessage('handshake-response', {
                'success': true,
                'myUrl': this.myWindow.location.href,
                'myOrigin': this.getMyOrigin(),
            });
        }
        getMyOrigin() {
            let origin;
            try {
                origin = this.myWindow.location.origin;
            }
            catch (e) {
                //Do nothing.
            }
            return origin !== null && origin !== void 0 ? origin : null;
        }
        isAllowedOrigin(origin) {
            if (this.handshakeDone && this.connectedOrigin) {
                //Use the connected origin.
                return Connection.originMatches(this.connectedOrigin, origin);
            }
            //Check all allowed origins.
            for (const allowedOrigin of this.allowedTargetOrigins) {
                if (Connection.originMatches(allowedOrigin, origin)) {
                    return true;
                }
            }
            return false;
        }
        static originMatches(required, input) {
            //Null and '*' allow all origins.
            if ((required === '*') || (required === null)) {
                return true;
            }
            return required === input;
        }
        validateHandshakeRequest(request) {
            return request.format === 'AmeAcCommunicator1' && request.tag === 'handshake-request';
        }
        validateHandshakeResponse(response) {
            return response.format === 'AmeAcCommunicator1' && response.tag === 'handshake-response';
        }
        completeHandshake(message, origin) {
            //Once the connection has been established, lock in the specific origin.
            if (this.connectedOrigin === null) {
                if (origin && (origin !== '*')) {
                    this.connectedOrigin = origin;
                }
                else if (message.myOrigin && (message.myOrigin !== '*')) {
                    this.connectedOrigin = message.myOrigin;
                }
            }
            this.handshakeDone = true;
            this.deferred.resolve(this);
            this.log('Handshake complete. Connected origin: ' + this.connectedOrigin);
        }
        createMessage(tag, props) {
            return Connection.combineObjects({
                'format': 'AmeAcCommunicator1',
                'tag': tag
            }, props);
        }
        /**
         * Combine the properties of two objects into a new object (shallow merge).
         *
         * This is implemented as a separate method only to work around some TypeScript
         * annoyances. Gradually constructing an object that obeys an interface is tricky.
         */
        static combineObjects(one, two) {
            let result = {};
            for (let key in one) {
                result[key] = one[key];
            }
            for (let key in two) {
                result[key] = two[key];
            }
            return result;
        }
        static getRandomString(length) {
            if (typeof crypto !== 'undefined') {
                try {
                    const bytes = crypto.getRandomValues(new Uint8Array(length));
                    let pieces = [];
                    for (let i = 0; i < bytes.length; i++) {
                        pieces.push(bytes[i].toString(36));
                    }
                    return pieces.join('').substring(0, length);
                }
                catch (e) {
                    //Fall through to the backup method.
                }
            }
            let result = '';
            while (result.length < length) {
                result += Math.random().toString(36).substring(2);
            }
            return result.substring(0, length);
        }
        handleRpcRequest(request) {
            var _a;
            if (!this.rpcMethods.hasOwnProperty(request.method)) {
                this.send(this.createMessage('rpc-response', {
                    'requestId': request.requestId,
                    'result': null,
                    'error': 'Unknown RPC method: ' + request.method
                }));
                return;
            }
            const method = this.rpcMethods[request.method];
            const args = (_a = request.args) !== null && _a !== void 0 ? _a : [];
            try {
                const result = method.apply(this, args);
                this.send(this.createMessage('rpc-response', {
                    'requestId': request.requestId,
                    'result': (typeof result === 'undefined') ? null : result,
                    'error': null
                }));
            }
            catch (e) {
                this.send(this.createMessage('rpc-response', {
                    'requestId': request.requestId,
                    'result': null,
                    'error': (e && e.message) ? e.message : e.toString()
                }));
            }
        }
        handleRpcResponse(response) {
            //Is this a response to a request we actually sent?
            const requestId = response.requestId;
            if (!this.pendingRpcRequests.hasOwnProperty(requestId)) {
                return;
            }
            const requestDef = this.pendingRpcRequests[requestId];
            if (requestDef.state() !== 'pending') {
                //This normally shouldn't happen.
                if (console && console.warn) {
                    console.warn('Received RPC response for request ID "' + requestId +
                        '", but the request has already been resolved.');
                }
                delete this.pendingRpcRequests[requestId];
                return;
            }
            if ((typeof response.error !== 'undefined') && (response.error !== null)) {
                requestDef.reject(response.error);
            }
            else {
                requestDef.resolve(response.result);
            }
            //The request should remove itself from the pending list,
            //but we'll do it here just in case.
            delete this.pendingRpcRequests[requestId];
        }
        get isConnected() {
            return this.handshakeDone && !this.isConnectionBroken;
        }
        log(message) {
            if (console && console.log) {
                console.log('AmeAcCommunicator (' + this.name + '): ' + message);
            }
        }
    }
    function parseMessage(data) {
        if ((typeof data !== 'object')
            || (typeof data.format !== 'string')
            || (typeof data.tag !== 'string')
            || (data.format !== MessageFormatName)) {
            return null;
        }
        return data;
    }
    function isHandshakeRequest(data) {
        return data.tag === 'handshake-request';
    }
    function isHandshakeResponse(data) {
        return (data.tag === 'handshake-response') && (typeof data.success === 'boolean');
    }
    function isRpcRequest(data) {
        return ((data.tag === 'rpc-request')
            && (typeof data.method === 'string')
            && (typeof data.args !== 'undefined')
            && (typeof data.requestId === 'string')
            && (Array.isArray(data.args)));
    }
    function isRpcResponse(data) {
        return ((data.tag === 'rpc-response')
            && (typeof data.result !== 'undefined')
            && (typeof data.requestId === 'string'));
    }
})(AmeAcCommunicator || (AmeAcCommunicator = {}));
//# sourceMappingURL=communicator.js.map