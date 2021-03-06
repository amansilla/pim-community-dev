import React, {ReactElement} from 'react';

type Provider<T = any> = [React.Provider<T>, T]; // TODO Fix the any to have working type-check.

export function composeProviders(...providers: Provider[]) {
    return ({children}: {children: ReactElement}) =>
        providers.reduce((children, [Provider, value]) => <Provider value={value}>{children}</Provider>, children);
}
