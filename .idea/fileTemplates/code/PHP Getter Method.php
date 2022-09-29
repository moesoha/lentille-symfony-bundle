public ${STATIC} function ${GET_OR_IS}${NAME}()#if(${RETURN_TYPE}): self#else#end {
#if (${STATIC} == "static")
    return self::$${FIELD_NAME};
#else
    return $this->${FIELD_NAME};
#end
}
